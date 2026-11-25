<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bike;
use App\Models\Rental;
use App\Services\RentalCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminRentalController extends Controller
{
    public function __construct(
        private readonly RentalCalculator $calculator
    ) {}

    public function dashboard(): View
    {
        $bikes = Bike::all();
        $totalStockAll = (int) $bikes->sum('stock_total');
        $availableStockAll = (int) $bikes->sum('stock_available');

        $pendingCount = Rental::where('status', 'pending')->count();
        $activeCount = Rental::where('status', 'active')->count();
        $completedCount = Rental::whereIn('status', ['returned', 'completed'])->count();

        $totalRentalRevenue = (int) Rental::whereIn('status', ['approved', 'active', 'returned', 'completed'])
            ->sum('rental_fee');
        $totalFineRevenue = (int) Rental::where('fine_paid', true)->sum('fine_amount');

        $activeRentals = Rental::with(['user', 'bike'])
            ->where('status', 'active')
            ->orderBy('due_time')
            ->get()
            ->map(function ($rental) {
                /** @var Rental $rental */
                $rental->reminder = $this->calculator->getReminderStatus($rental);

                return $rental;
            });

        return view('admin.dashboard', compact(
            'bikes',
            'totalStockAll',
            'availableStockAll',
            'pendingCount',
            'activeCount',
            'completedCount',
            'totalRentalRevenue',
            'totalFineRevenue',
            'activeRentals'
        ));
    }

    public function index(Request $request): View
    {
        $query = Rental::with(['user', 'bike', 'paymentMethod', 'approver', 'returner']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $rentals = $query->latest()->get()->map(function ($rental) {
            /** @var Rental $rental */
            $rental->reminder = $rental->status === 'active'
                ? $this->calculator->getReminderStatus($rental)
                : null;

            $rental->live_fine = $rental->status === 'active'
                ? $this->calculator->calculateFine($rental)
                : null;

            return $rental;
        });

        return view('admin.rentals.index', compact('rentals'));
    }

    public function approve(Rental $rental): RedirectResponse
    {
        try {
            DB::transaction(function () use ($rental) {
                $lockedRental = Rental::whereKey($rental->id)->lockForUpdate()->firstOrFail();

                if ($lockedRental->status !== 'pending') {
                    throw new \RuntimeException('Hanya peminjaman berstatus pending yang dapat disetujui.');
                }

                $lockedBike = Bike::whereKey($lockedRental->bike_id)->lockForUpdate()->firstOrFail();

                if ($lockedBike->status !== 'available' || $lockedBike->stock_available < 1) {
                    throw new \RuntimeException('Stok sepeda tidak tersedia untuk disetujui.');
                }

                $startTime = now();
                $dueTime = (clone $startTime)->addHours((int) $lockedRental->duration_hours);

                $lockedBike->decrement('stock_available', 1);

                $lockedRental->update([
                    'status' => 'active',
                    'start_time' => $startTime,
                    'due_time' => $dueTime,
                    'approved_by' => Auth::id(),
                ]);
            });

            return back()->with('success', "Peminjaman #{$rental->id} disetujui. Stok sepeda dikurangi 1 dan waktu sewa telah dimulai.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Rental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($rental, $validated) {
                $lockedRental = Rental::whereKey($rental->id)->lockForUpdate()->firstOrFail();

                if ($lockedRental->status !== 'pending') {
                    throw new \RuntimeException('Hanya peminjaman berstatus pending yang dapat ditolak.');
                }

                $lockedRental->update([
                    'status' => 'rejected',
                    'admin_note' => $validated['admin_note'] ?? 'Pengajuan ditolak oleh admin.',
                    'approved_by' => Auth::id(),
                ]);
            });

            return back()->with('success', "Peminjaman #{$rental->id} telah ditolak.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function returnBike(Request $request, Rental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'bike_condition_on_return' => ['required', 'in:good,damaged,lost'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($rental, $validated) {
                $lockedRental = Rental::whereKey($rental->id)->lockForUpdate()->firstOrFail();

                if ($lockedRental->status !== 'active') {
                    throw new \RuntimeException('Hanya peminjaman aktif yang dapat diproses pengembaliannya.');
                }

                $lockedBike = Bike::whereKey($lockedRental->bike_id)->lockForUpdate()->firstOrFail();
                $returnedAt = now();

                $fineCalc = $this->calculator->calculateFine($lockedRental, $returnedAt);
                $fineAmount = (int) $fineCalc['fine_amount'];
                $lateMinutes = (int) $fineCalc['late_minutes'];

                $condition = $validated['bike_condition_on_return'];

                if ($condition === 'good') {
                    $lockedBike->increment('stock_available', 1);
                } elseif ($condition === 'damaged') {
                    $lockedBike->update([
                        'status' => 'unavailable',
                    ]);
                } elseif ($condition === 'lost') {
                    $newTotal = max(0, (int) $lockedBike->stock_total - 1);
                    $newAvailable = min((int) $lockedBike->stock_available, $newTotal);

                    $lockedBike->update([
                        'stock_total' => $newTotal,
                        'stock_available' => $newAvailable,
                    ]);
                }

                $nextStatus = $fineAmount === 0 ? 'completed' : 'returned';

                $lockedRental->update([
                    'returned_at' => $returnedAt,
                    'late_minutes' => $lateMinutes,
                    'fine_amount' => $fineAmount,
                    'fine_paid' => $fineAmount === 0,
                    'bike_condition_on_return' => $condition,
                    'admin_note' => $validated['admin_note'] ?? $lockedRental->admin_note,
                    'status' => $nextStatus,
                    'returned_by' => Auth::id(),
                ]);
            });

            return back()->with('success', "Pengembalian peminjaman #{$rental->id} berhasil diproses.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markFinePaid(Rental $rental): RedirectResponse
    {
        if ($rental->fine_amount <= 0) {
            return back()->with('error', 'Peminjaman ini tidak memiliki denda.');
        }

        if ($rental->fine_paid) {
            return back()->with('error', 'Denda sudah ditandai lunas.');
        }

        $rental->update([
            'fine_paid' => true,
            'status' => 'completed',
        ]);

        return back()->with('success', "Pembayaran denda peminjaman #{$rental->id} telah diverifikasi lunas.");
    }
}
