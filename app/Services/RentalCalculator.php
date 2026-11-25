<?php

namespace App\Services;

use App\Models\Rental;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class RentalCalculator
{
    public const FINE_PERCENT_PER_BLOCK = 0.10; // 10%
    public const BLOCK_MINUTES = 10; // per 10 menit
    public const REMINDER_THRESHOLD_MINUTES = 15; // ingatkan saat sisa <= 15 menit

    /**
     * Hitung total biaya sewa awal berdasarkan durasi jam dan harga per jam.
     */
    public function calculateRentalFee(int $durationHours, int $pricePerHour): int
    {
        return max(0, $durationHours) * max(0, $pricePerHour);
    }

    /**
     * Hitung tarif denda per blok 10 menit (10% dari harga per jam sepeda).
     */
    public function calculateFinePerBlock(int $pricePerHour): int
    {
        return (int) round(max(0, $pricePerHour) * self::FINE_PERCENT_PER_BLOCK);
    }

    /**
     * Hitung keterlambatan dan total denda berdasarkan waktu acuan (default: waktu sekarang).
     *
     * @return array{
     *     late_minutes: int,
     *     blocks: int,
     *     fine_per_block: int,
     *     fine_amount: int
     * }
     */
    public function calculateFine(Rental $rental, ?CarbonInterface $referenceTime = null): array
    {
        $finePerBlock = $this->calculateFinePerBlock((int) $rental->locked_price_per_hour);

        if (! $rental->due_time) {
            return [
                'late_minutes' => 0,
                'blocks' => 0,
                'fine_per_block' => $finePerBlock,
                'fine_amount' => 0,
            ];
        }

        $now = $referenceTime
            ? Carbon::instance($referenceTime)
            : ($rental->returned_at ? Carbon::instance($rental->returned_at) : now());

        if ($now->lessThanOrEqualTo($rental->due_time)) {
            return [
                'late_minutes' => 0,
                'blocks' => 0,
                'fine_per_block' => $finePerBlock,
                'fine_amount' => 0,
            ];
        }

        // Hitung selisih menit lewat dari due_time (dibulatkan ke atas per menit penuh)
        $lateMinutes = (int) ceil($rental->due_time->floatDiffInMinutes($now));
        $lateMinutes = max(1, $lateMinutes);

        $blocks = (int) ceil($lateMinutes / self::BLOCK_MINUTES);
        $fineAmount = $blocks * $finePerBlock;

        return [
            'late_minutes' => $lateMinutes,
            'blocks' => $blocks,
            'fine_per_block' => $finePerBlock,
            'fine_amount' => $fineAmount,
        ];
    }

    /**
     * Status pengingat waktu untuk peminjaman aktif.
     *
     * @return array{
     *     state: string,
     *     minutes_remaining: int,
     *     late_minutes: int,
     *     estimated_fine: int,
     *     message: string
     * }
     */
    public function getReminderStatus(Rental $rental, ?CarbonInterface $now = null): array
    {
        $current = $now ? Carbon::instance($now) : now();

        if (! $rental->due_time) {
            return [
                'state' => 'inactive',
                'minutes_remaining' => 0,
                'late_minutes' => 0,
                'estimated_fine' => 0,
                'message' => 'Menunggu jadwal mulai peminjaman.',
            ];
        }

        if ($current->greaterThan($rental->due_time)) {
            $fineData = $this->calculateFine($rental, $current);

            return [
                'state' => 'overdue',
                'minutes_remaining' => 0,
                'late_minutes' => $fineData['late_minutes'],
                'estimated_fine' => $fineData['fine_amount'],
                'message' => "Batas waktu terlewat {$fineData['late_minutes']} menit. Estimasi denda: Rp " . number_format($fineData['fine_amount'], 0, ',', '.'),
            ];
        }

        $minutesRemaining = (int) max(0, floor($current->floatDiffInMinutes($rental->due_time)));

        if ($minutesRemaining <= self::REMINDER_THRESHOLD_MINUTES) {
            return [
                'state' => 'warning',
                'minutes_remaining' => $minutesRemaining,
                'late_minutes' => 0,
                'estimated_fine' => 0,
                'message' => "Pengingat: sisa waktu peminjaman tinggal {$minutesRemaining} menit lagi.",
            ];
        }

        return [
            'state' => 'normal',
            'minutes_remaining' => $minutesRemaining,
            'late_minutes' => 0,
            'estimated_fine' => 0,
            'message' => "Sisa waktu peminjaman {$minutesRemaining} menit.",
        ];
    }
}
