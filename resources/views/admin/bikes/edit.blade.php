<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Sepeda — {{ $bike->name }}</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.bikes.update', $bike) }}" enctype="multipart/form-data"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Sepeda</label>
                    <input type="text" name="code" value="{{ old('code', $bike->code) }}"
                           class="w-full border-gray-300 rounded-lg text-sm">
                    @error('code') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Sepeda</label>
                    <input type="text" name="type" value="{{ old('type', $bike->type) }}"
                           class="w-full border-gray-300 rounded-lg text-sm">
                    @error('type') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sepeda</label>
                <input type="text" name="name" value="{{ old('name', $bike->name) }}"
                       class="w-full border-gray-300 rounded-lg text-sm">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tarif / Jam (Rp)</label>
                    <input type="number" name="price_per_hour" value="{{ old('price_per_hour', $bike->price_per_hour) }}"
                           class="w-full border-gray-300 rounded-lg text-sm">
                    @error('price_per_hour') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Stok</label>
                    <input type="number" name="stock_total" value="{{ old('stock_total', $bike->stock_total) }}"
                           class="w-full border-gray-300 rounded-lg text-sm">
                    @error('stock_total') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Tersedia</label>
                    <input type="number" name="stock_available" value="{{ old('stock_available', $bike->stock_available) }}"
                           class="w-full border-gray-300 rounded-lg text-sm">
                    @error('stock_available') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-lg text-sm">
                    <option value="available" @selected(old('status', $bike->status) === 'available')>Available</option>
                    <option value="unavailable" @selected(old('status', $bike->status) === 'unavailable')>Unavailable</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ganti Foto Sepeda (Opsional)</label>
                <input type="file" name="image" accept="image/*" class="w-full text-sm border border-gray-300 rounded-lg p-1.5">
                @error('image') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border-gray-300 rounded-lg text-sm">{{ old('description', $bike->description) }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.bikes.index') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Batal</a>
                <button type="submit" class="px-5 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-500">Update</button>
            </div>
        </form>
    </div>
</x-app-layout>
