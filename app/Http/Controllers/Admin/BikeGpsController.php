<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bike;
use Illuminate\Http\Request;

class BikeGpsController extends Controller
{
    public function index()
    {
        $activeBikes = $this->getBorrowedBikes();

        return view('admin.bikes.gps', [
            'activeBikes' => $activeBikes,
            'mapsApiKey' => config('services.google_maps.key'),
        ]);
    }

    public function positions()
    {
        return response()->json([
            'bikes' => $this->getBorrowedBikes(),
        ]);
    }

    public function updateLocation(Request $request, Bike $bike)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $bike->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'gps_updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Koordinat GPS sepeda berhasil diperbarui.',
            'bike' => $bike->fresh(),
        ]);
    }

    private function getBorrowedBikes()
    {
        // Sesuaikan status di whereIn dengan nilai status sewa aktif di AdminRentalController
        $activeStatuses = ['active', 'borrowed', 'ongoing', 'approved'];

        return Bike::query()
            ->whereHas('rentals', function ($query) use ($activeStatuses) {
                $query->whereIn('status', $activeStatuses);
            })
            ->with(['rentals' => function ($query) use ($activeStatuses) {
                $query->whereIn('status', $activeStatuses)
                    ->latest()
                    ->with('user:id,name,email,phone')
                    ->limit(1);
            }])
            ->get();
    }
}
