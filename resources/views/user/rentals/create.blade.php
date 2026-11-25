<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ajukan Peminjaman{{ $bike ? ' — ' . $bike->name : '' }}
            </h2>
            <a href="{{ route('user.dashboard') }}" class="text-sm text-gray-600 hover:underline">
                &larr; Kembali ke Katalog
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if (! $bike && $bikes->isEmpty())
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                Belum ada sepeda dengan stok tersedia untuk diajukan.
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            <form method="POST"
                  action="{{ $bike ? route('user.rentals.store', $bike) : route('user.rentals.quick-store') }}"
                  enctype="multipart/form-data" class="space-y-5">
                @csrf

                @unless ($bike)
                    <div>
                        <label for="bike_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Sepeda</label>
                        <select id="bike_id" name="bike_id" required onchange="updateBikeSelection()"
                                class="w-full border-gray-300 rounded-lg text-sm">
                            <option value="">-- Pilih sepeda --</option>
                            @foreach ($bikes as $availableBike)
                                <option value="{{ $availableBike->id }}"
                                        data-name="{{ $availableBike->name }}"
                                        data-type="{{ $availableBike->type }}"
                                        data-price="{{ (int) $availableBike->price_per_hour }}"
                                        data-stock="{{ $availableBike->stock_available }}"
                                        @selected(old('bike_id') == $availableBike->id)>
                                    {{ $availableBike->name }} ({{ $availableBike->type }}) — Rp {{ number_format($availableBike->price_per_hour, 0, ',', '.') }}/jam
                                </option>
                            @endforeach
                        </select>
                        @error('bike_id')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endunless

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-1 text-sm">
                    <p id="bikeSummaryName" class="font-semibold text-gray-900 text-base">
                        {{ $bike ? $bike->name . ' (' . $bike->type . ')' : 'Pilih sepeda untuk melihat tarif' }}
                    </p>
                    <p class="text-gray-700">Tarif per jam: <strong id="bikeSummaryPrice">{{ $bike ? 'Rp ' . number_format($bike->price_per_hour, 0, ',', '.') : '—' }}</strong></p>
                    <p class="text-gray-700">
                        Denda keterlambatan: <strong id="bikeSummaryFine">{{ $bike ? 'Rp ' . number_format((int) round($bike->price_per_hour * 0.10), 0, ',', '.') : '—' }}</strong> per 10 menit (10% dari tarif/jam)
                    </p>
                    <p class="text-gray-600">Stok tersedia: <span id="bikeSummaryStock">{{ $bike ? $bike->stock_available . ' unit' : '—' }}</span></p>
                </div>

                {{-- Daftar Rekening & E-Wallet Tujuan --}}
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-gray-800">Rekening / E-Wallet Pembayaran:</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($paymentMethods as $method)
                            <div class="p-3 rounded-lg border border-gray-200 text-sm">
                                <p class="font-bold text-gray-900">{{ $method->name }}</p>
                                <p class="text-gray-700">No: <span class="font-mono">{{ $method->account_number }}</span></p>
                                <p class="text-xs text-gray-500">a.n. {{ $method->account_holder }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="duration_hours" class="block text-sm font-medium text-gray-700 mb-1">
                        Durasi Peminjaman (Jam)
                    </label>
                    <input type="number" id="duration_hours" name="duration_hours"
                           min="1" max="24" value="{{ old('duration_hours', 1) }}"
                           oninput="updateEstimate()"
                           class="w-full border-gray-300 rounded-lg text-sm">
                    @error('duration_hours')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-4 rounded-lg bg-indigo-50 text-indigo-900 text-sm">
                    Estimasi Total Biaya Sewa: <strong id="totalEstimate">Rp {{ number_format(($bike->price_per_hour ?? 0) * (int) old('duration_hours', 1), 0, ',', '.') }}</strong>
                </div>

                <div>
                    <label for="payment_method_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Metode Pembayaran
                    </label>
                    <select id="payment_method_id" name="payment_method_id" class="w-full border-gray-300 rounded-lg text-sm">
                        <option value="">-- Pilih Rekening / E-Wallet --</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}" @selected(old('payment_method_id') == $method->id)>
                                {{ $method->name }} — {{ $method->account_number }} ({{ $method->account_holder }})
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-1">
                        Upload Bukti Pembayaran (JPG, PNG, WEBP — max 2MB)
                    </label>
                    <input type="file" id="payment_proof" name="payment_proof" accept="image/*"
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg p-2">
                    @error('payment_proof')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('user.dashboard') }}" class="px-4 py-2 text-sm text-gray-700 rounded-lg border border-gray-300">
                        Batal
                    </a>
                    <button type="submit" @disabled(! $bike && $bikes->isEmpty())
                            class="px-5 py-2 text-sm bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let ratePerHour = {{ (int) ($bike->price_per_hour ?? 0) }};

        function updateBikeSelection() {
            const select = document.getElementById('bike_id');
            const option = select.options[select.selectedIndex];

            ratePerHour = Number(option.dataset.price || 0);
            document.getElementById('bikeSummaryName').textContent = option.value
                ? `${option.dataset.name} (${option.dataset.type})`
                : 'Pilih sepeda untuk melihat tarif';
            document.getElementById('bikeSummaryPrice').textContent = option.value
                ? 'Rp ' + ratePerHour.toLocaleString('id-ID')
                : '—';
            document.getElementById('bikeSummaryFine').textContent = option.value
                ? 'Rp ' + Math.round(ratePerHour * 0.10).toLocaleString('id-ID')
                : '—';
            document.getElementById('bikeSummaryStock').textContent = option.value
                ? `${option.dataset.stock} unit`
                : '—';
            updateEstimate();
        }

        function updateEstimate() {
            const hours = Math.max(1, parseInt(document.getElementById('duration_hours').value || '1', 10));
            const total = hours * ratePerHour;
            document.getElementById('totalEstimate').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        if (document.getElementById('bike_id')?.value) {
            updateBikeSelection();
        }
    </script>
</x-app-layout>
