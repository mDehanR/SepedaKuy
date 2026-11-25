<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Pengajuan Peminjaman #{{ $rental->id }}
            </h2>
            <a href="{{ route('user.rentals.index') }}" class="text-sm text-gray-600 hover:underline">
                &larr; Kembali ke Peminjaman Saya
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('user.rentals.update', $rental) }}"
              class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                <p class="font-semibold text-gray-900">Pengajuan masih menunggu persetujuan</p>
                <p class="mt-1 text-gray-600">
                    Pilihan sepeda dan durasi dapat diubah sebelum admin menyetujui pengajuan.
                </p>
            </div>

            <div>
                <label for="bike_id" class="block text-sm font-medium text-gray-700 mb-1">Sepeda</label>
                <select id="bike_id" name="bike_id" required onchange="updateRentalEstimate()"
                        class="w-full border-gray-300 rounded-lg text-sm">
                    @foreach ($bikes as $availableBike)
                        <option value="{{ $availableBike->id }}"
                                data-price="{{ $availableBike->id === $rental->bike_id ? $rental->locked_price_per_hour : $availableBike->price_per_hour }}"
                                @selected(old('bike_id', $rental->bike_id) == $availableBike->id)>
                            {{ $availableBike->name }} ({{ $availableBike->type }}) — Rp
                            {{ number_format($availableBike->id === $rental->bike_id ? $rental->locked_price_per_hour : $availableBike->price_per_hour, 0, ',', '.') }}/jam
                        </option>
                    @endforeach
                </select>
                @error('bike_id')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="duration_hours" class="block text-sm font-medium text-gray-700 mb-1">
                    Durasi Peminjaman (Jam)
                </label>
                <input type="number" id="duration_hours" name="duration_hours" min="1" max="24"
                       value="{{ old('duration_hours', $rental->duration_hours) }}"
                       oninput="updateRentalEstimate()" required
                       class="w-full border-gray-300 rounded-lg text-sm">
                @error('duration_hours')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-lg bg-indigo-50 p-4 text-sm text-indigo-900">
                Estimasi biaya sewa baru:
                <strong id="totalEstimate">Rp {{ number_format($rental->rental_fee, 0, ',', '.') }}</strong>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                Jika nominal berubah, bukti pembayaran yang sudah diunggah tetap tersimpan. Admin akan meninjau
                kesesuaian pembayaran dengan biaya terbaru.
                @if ($rental->payment_proof)
                    <a href="{{ asset('storage/' . $rental->payment_proof) }}" target="_blank" rel="noopener"
                       class="mt-2 block font-semibold underline">Lihat bukti pembayaran saat ini</a>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('user.rentals.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700">Batal</a>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script>
        function updateRentalEstimate() {
            const bikeSelect = document.getElementById('bike_id');
            const selectedBike = bikeSelect.options[bikeSelect.selectedIndex];
            const pricePerHour = Number(selectedBike.dataset.price || 0);
            const hours = Math.max(1, parseInt(document.getElementById('duration_hours').value || '1', 10));

            document.getElementById('totalEstimate').textContent =
                'Rp ' + (hours * pricePerHour).toLocaleString('id-ID');
        }

        updateRentalEstimate();
    </script>
</x-app-layout>
