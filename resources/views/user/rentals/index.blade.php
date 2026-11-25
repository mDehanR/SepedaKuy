<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Peminjaman Saya
            </h2>
            <a href="{{ route('user.rentals.quick-create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-500">
                + Pinjam Sepeda Lagi
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if (session('success'))
            <div class="p-4 rounded-lg bg-green-100 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-100 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @forelse ($rentals as $rental)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 border-b border-gray-100 pb-3">
                    <div>
                        <span class="text-xs text-gray-500">Transaksi #{{ $rental->id }}</span>
                        <h3 class="font-bold text-gray-900 text-lg">
                            {{ $rental->bike->name }} ({{ $rental->bike->type }})
                        </h3>
                    </div>
                    <div class="flex items-center gap-2 self-start md:self-auto">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full uppercase bg-gray-100 text-gray-800">
                            {{ $rental->status }}
                        </span>
                        @if ($rental->status === 'pending')
                            <a href="{{ route('user.rentals.edit', $rental) }}"
                               class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:border-red-300 hover:text-red-700">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-gray-700">
                    <div>
                        <span class="text-gray-500 block text-xs">Durasi & Tarif Terkunci</span>
                        {{ $rental->duration_hours }} jam @ Rp {{ number_format($rental->locked_price_per_hour, 0, ',', '.') }}
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Biaya Sewa</span>
                        <strong>Rp {{ number_format($rental->rental_fee, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Waktu Mulai / Batas Kembali</span>
                        @if ($rental->start_time && $rental->due_time)
                            {{ $rental->start_time->format('d M H:i') }} &rarr; <strong>{{ $rental->due_time->format('d M H:i') }}</strong>
                        @else
                            <span class="text-gray-400">Menunggu persetujuan</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Denda</span>
                        @php
                            $displayFine = $rental->status === 'active'
                                ? ($rental->live_fine['fine_amount'] ?? 0)
                                : $rental->fine_amount;
                        @endphp
                        <strong class="{{ $displayFine > 0 ? 'text-red-600' : 'text-gray-800' }}">
                            Rp {{ number_format($displayFine, 0, ',', '.') }}
                        </strong>
                        @if ($rental->status === 'active' && $displayFine > 0)
                            <span class="block text-xs text-red-500">(Estimasi berjalan)</span>
                        @elseif ($rental->fine_amount > 0)
                            <span class="block text-xs {{ $rental->fine_paid ? 'text-green-600' : 'text-red-600' }}">
                                {{ $rental->fine_paid ? 'Lunas' : 'Belum lunas' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Pengingat Waktu Jika Sedang Aktif --}}
                @if ($rental->status === 'active' && !empty($rental->reminder))
                    @php
                        $state = $rental->reminder['state'];
                        $alertClass = match ($state) {
                            'overdue' => 'bg-red-50 border-red-300 text-red-800',
                            'warning' => 'bg-yellow-50 border-yellow-300 text-yellow-900',
                            default => 'bg-blue-50 border-blue-200 text-blue-800',
                        };
                    @endphp
                    <div class="p-3 rounded-lg border text-sm font-medium {{ $alertClass }}">
                        {{ $rental->reminder['message'] }}
                    </div>
                @endif

                @if ($rental->admin_note)
                    <p class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg">
                        Catatan Admin: {{ $rental->admin_note }}
                    </p>
                @endif

                {{-- Form Upload Bukti Denda Jika Ada Denda Belum Lunas --}}
                @if ($rental->fine_amount > 0 && ! $rental->fine_paid)
                    <form method="POST" action="{{ route('user.rentals.pay-fine', $rental) }}"
                          enctype="multipart/form-data"
                          class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
                        @csrf
                        <input type="file" name="fine_payment_proof" accept="image/*" required
                               class="text-xs border border-gray-300 rounded-lg p-1.5">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-500">
                            Upload Bukti Bayar Denda
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white p-8 rounded-xl text-center text-gray-500 text-sm">
                Kamu belum memiliki riwayat peminjaman sepeda.
            </div>
        @endforelse
    </div>
</x-app-layout>
