<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminBikeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Bike::query();

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

        $bikes = $query->orderBy('type')->orderBy('code')->get();
        $bikeTypes = Bike::query()->distinct()->pluck('type');

        return view('admin.bikes.index', compact('bikes', 'bikeTypes'));
    }

    public function create(): View
    {
        return view('admin.bikes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:bikes,code'],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'max:60'],
            'price_per_hour' => ['required', 'integer', 'min:1000'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'stock_available' => ['required', 'integer', 'min:0', 'lte:stock_total'],
            'status' => ['required', 'in:available,unavailable'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('bikes', 'public');
        }

        Bike::create($validated);

        return redirect()->route('admin.bikes.index')
            ->with('success', 'Data sepeda berhasil ditambahkan.');
    }

    public function edit(Bike $bike): View
    {
        return view('admin.bikes.edit', compact('bike'));
    }

    public function update(Request $request, Bike $bike): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:bikes,code,' . $bike->id],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'max:60'],
            'price_per_hour' => ['required', 'integer', 'min:1000'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'stock_available' => ['required', 'integer', 'min:0', 'lte:stock_total'],
            'status' => ['required', 'in:available,unavailable'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('image')) {
            if ($bike->image) {
                Storage::disk('public')->delete($bike->image);
            }
            $validated['image'] = $request->file('image')->store('bikes', 'public');
        }

        $bike->update($validated);

        return redirect()->route('admin.bikes.index')
            ->with('success', 'Data sepeda berhasil diperbarui.');
    }

    public function destroy(Bike $bike): RedirectResponse
    {
        if ($bike->image) {
            Storage::disk('public')->delete($bike->image);
        }

        $bike->delete();

        return redirect()->route('admin.bikes.index')
            ->with('success', 'Data sepeda berhasil dihapus dari katalog admin, sementara data peminjaman user tetap tersimpan.');
    }
}
