<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bike;
use App\Models\PaymentMethod;
use App\Models\Rental;
use App\Services\RentalCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserRentalController extends Controller
{
    public function __construct(
        private readonly RentalCalculator $calculator
    ) {}

    public function index(Request $request): View
    {
        $query = Bike::query()->where('status', 'available');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $bikes = $query->orderBy('type')->orderBy('name')->get();
        $bikeTypes = Bike::query()
            ->where('status', 'available')
            ->distinct()
            ->pluck('type');

        $activeRentals = Rental::with(['bike', 'paymentMethod'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->latest()
            ->get()
            ->map(function ($rental) {
                /** @var Rental $rental */
                $rental->reminder = $this->calculator->getReminderStatus($rental);

                return $rental;
            });

        return view('user.dashboard', compact('bikes', 'bikeTypes', 'activeRentals'));
    }

    public function create(Bike $bike): View|RedirectResponse
    {
        if ($bike->status !== 'available' || $bike->stock_available < 1) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Sepeda ini sedang tidak tersedia atau stok habis.');
        }

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('user.rentals.create', compact('bike', 'paymentMethods'));
    }

    public function quickCreate(): View
    {
        $bike = null;
        $bikes = Bike::query()
            ->where('status', 'available')
            ->where('stock_available', '>', 0)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('user.rentals.create', compact('bike', 'bikes', 'paymentMethods'));
    }

    public function store(Request $request, Bike $bike): RedirectResponse
    {
        $validated = $request->validate($this->rentalRules());

        return $this->createRental($request, $bike, $validated);
    }

    public function quickStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bike_id' => ['required', 'exists:bikes,id'],
            ...$this->rentalRules(),
        ]);
        $bike = Bike::findOrFail($validated['bike_id']);

        return $this->createRental($request, $bike, $validated);
    }

    public function edit(Rental $rental): View|RedirectResponse
    {
        abort_if($rental->user_id !== Auth::id(), 403);

        if ($rental->status !== 'pending') {
            return redirect()->route('user.rentals.index')
                ->with('error', 'Pengajuan hanya dapat diubah sebelum disetujui admin.');
        }

        $bikes = Bike::query()
            ->where(function ($query) use ($rental) {
                $query->where(function ($query) {
                    $query->where('status', 'available')->where('stock_available', '>', 0);
                })->orWhereKey($rental->bike_id);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('user.rentals.edit', compact('rental', 'bikes'));
    }

    public function update(Request $request, Rental $rental): RedirectResponse
    {
        abort_if($rental->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'bike_id' => ['required', 'exists:bikes,id'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        try {
            DB::transaction(function () use ($validated, $rental) {
                $lockedRental = Rental::whereKey($rental->id)->lockForUpdate()->firstOrFail();

                if ($lockedRental->user_id !== Auth::id()) {
                    abort(403);
                }

                if ($lockedRental->status !== 'pending') {
                    throw new \RuntimeException('Pengajuan hanya dapat diubah sebelum disetujui admin.');
                }

                $lockedBike = Bike::whereKey($validated['bike_id'])->lockForUpdate()->firstOrFail();

                if ($lockedBike->status !== 'available' || $lockedBike->stock_available < 1) {
                    throw new \RuntimeException('Sepeda yang dipilih sedang tidak tersedia atau stoknya habis.');
                }

                $durationHours = (int) $validated['duration_hours'];
                $lockedPricePerHour = $lockedBike->id === $lockedRental->bike_id
                    ? (int) $lockedRental->locked_price_per_hour
                    : (int) $lockedBike->price_per_hour;

                $lockedRental->update([
                    'bike_id' => $lockedBike->id,
                    'duration_hours' => $durationHours,
                    'locked_price_per_hour' => $lockedPricePerHour,
                    'rental_fee' => $this->calculator->calculateRentalFee($durationHours, $lockedPricePerHour),
                ]);
            });

            return redirect()->route('user.rentals.index')
                ->with('success', "Pengajuan peminjaman #{$rental->id} berhasil diperbarui.");
        } catch (\RuntimeException $e) {
            return redirect()->route('user.rentals.index')->with('error', $e->getMessage());
        }
    }

    public function myRentals(): View
    {
        $rentals = Rental::with(['bike', 'paymentMethod'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($rental) {
                /** @var Rental $rental */
                $rental->reminder = $rental->status === 'active'
                    ? $this->calculator->getReminderStatus($rental)
                    : null;

                $rental->live_fine = $rental->status === 'active'
                    ? $this->calculator->calculateFine($rental)
                    : null;

                return $rental;
            });

        return view('user.rentals.index', compact('rentals'));
    }

    public function payFine(Request $request, Rental $rental): RedirectResponse
    {
        abort_if($rental->user_id !== Auth::id(), 403);

        $request->validate([
            'fine_payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($rental->fine_amount <= 0) {
            return back()->with('error', 'Tidak ada tagihan denda untuk peminjaman ini.');
        }

        if ($rental->fine_paid) {
            return back()->with('error', 'Denda untuk peminjaman ini sudah dibayar.');
        }

        $fineProofPath = $request->file('fine_payment_proof')->store('fine_proofs', 'public');

        $rental->update([
            'fine_payment_proof' => $fineProofPath,
        ]);

        return back()->with('success', 'Bukti pembayaran denda berhasil diunggah. Menunggu verifikasi admin.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rentalRules(): array
    {
        return [
            'duration_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createRental(Request $request, Bike $bike, array $validated): RedirectResponse
    {
        try {
            $rental = DB::transaction(function () use ($validated, $bike, $request) {
                $lockedBike = Bike::whereKey($bike->id)->lockForUpdate()->firstOrFail();

                if ($lockedBike->status !== 'available' || $lockedBike->stock_available < 1) {
                    throw new \RuntimeException('Stok sepeda tidak mencukupi saat pengajuan diproses.');
                }

                $paymentMethod = PaymentMethod::where('id', $validated['payment_method_id'])
                    ->where('is_active', true)
                    ->firstOrFail();

                $durationHours = (int) $validated['duration_hours'];
                $lockedPricePerHour = (int) $lockedBike->price_per_hour;
                $rentalFee = $this->calculator->calculateRentalFee($durationHours, $lockedPricePerHour);
                $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

                return Rental::create([
                    'user_id' => Auth::id(),
                    'bike_id' => $lockedBike->id,
                    'payment_method_id' => $paymentMethod->id,
                    'duration_hours' => $durationHours,
                    'locked_price_per_hour' => $lockedPricePerHour,
                    'rental_fee' => $rentalFee,
                    'late_minutes' => 0,
                    'fine_amount' => 0,
                    'fine_paid' => false,
                    'payment_proof' => $proofPath,
                    'status' => 'pending',
                ]);
            });

            return redirect()->route('user.rentals.index')
                ->with('success', "Pengajuan peminjaman #{$rental->id} berhasil dikirim. Menunggu konfirmasi admin.");
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
