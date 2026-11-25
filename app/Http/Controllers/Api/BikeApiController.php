<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bike;
use App\Models\Rental;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BikeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Bike::query();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $bikes = $query->orderBy('code')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar data sepeda SepedaKuy',
            'data' => $bikes,
        ]);
    }

    public function show(Bike $bike): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail sepeda',
            'data' => $bike,
        ]);
    }

    public function activeRentals(): JsonResponse
    {
        $rentals = Rental::with([
            'user:id,name,email,phone',
            'bike:id,code,name,type,price_per_hour,stock_available',
        ])
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar peminjaman berjalan',
            'data' => $rentals,
        ]);
    }
}
