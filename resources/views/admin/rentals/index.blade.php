<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manajemen Peminjaman & Pengembalian
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if (session('success'))
            <div class="p-4 rounded-lg bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-100 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        @forelse ($rentals as $rental)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 pb-3">
                    <div>
                        <span class="text-xs text-gray-500">#{{ $rental->id }}</span>
                        <h3 class="font-bold text-gray-900">
                            {{ $rental->user->name }} ({{ $rental->user->email }}) — {{ $rental->bike->name }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            Durasi: {{ $rental->duration_hours }} jam | Tarif terkunci: Rp {{ number_format($rental->locked_price_per_hour, 0, ',', '.') }}/jam | Total Sewa: <strong>Rp {{ number_format($rental->rental_fee, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full uppercase bg-gray-100 text-gray-800 self-start sm:self-auto">
                        {{ $rental->status }}
                    </span>
                </div>

                {{-- Bukti Pembayaran & Waktu --}}
                <div class="flex flex-wrap gap-4 text-xs text-gray-700 items-center">
                    @if ($rental->payment_proof)
                        <a href="{{ asset('storage/' . $rental->payment_proof) }}" target="_blank"
                           class="px-3 py-1.5 bg-gray-100 rounded-lg hover:bg-gray-200 font-medium">
                            Lihat Bukti Bayar Sewa
                        </a>
                    @endif
                    @if ($rental->fine_payment_proof)
                        <a href="{{ asset('storage/' . $rental->fine_payment_proof) }}" target="_blank"
                           class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-medium">
                            Lihat Bukti Bayar Denda
                        </a>
                    @endif
                    @if ($rental->due_time)
                        <span>Batas Kembali: <strong>{{ $rental->due_time->format('d M Y H:i') }}</strong></span>
                    @endif
                    @if ($rental->status === 'active')
                        <span class="font-semibold {{ ($rental->live_fine['fine_amount'] ?? 0) > 0 ? 'text-red-600' : 'text-blue-700' }}">
                            Estimasi Denda Saat Ini: Rp {{ number_format($rental->live_fine['fine_amount'] ?? 0, 0, ',', '.') }}
                        </span>
                    @elseif ($rental->fine_amount > 0)
                        <span class="font-semibold text-red-600">
                            Denda: Rp {{ number_format($rental->fine_amount, 0, ',', '.') }} ({{ $rental->fine_paid ? 'Lunas' : 'Belum Lunas' }})
                        </span>
                    @endif
                </div>

                {{-- Aksi Pending: Approve / Reject --}}
                @if ($rental->status === 'pending')
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <form method="POST" action="{{ route('admin.rentals.approve', $rental) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-500">
                                Setujui & Mulai Sewa
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.rentals.reject', $rental) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="admin_note" placeholder="Alasan penolakan (opsional)"
                                   class="border-gray-300 rounded-lg text-xs">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-500">
                                Tolak
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Aksi Active: Kembalikan Sepeda --}}
                @if ($rental->status === 'active')
                    <form method="POST" action="{{ route('admin.rentals.return', $rental) }}"
                          class="pt-3 border-t border-gray-100 flex flex-wrap items-center gap-3">
                        @csrf
                        <select name="bike_condition_on_return" class="border-gray-300 rounded-lg text-xs">
                            <option value="good">Kondisi Baik (Stok +1)</option>
                            <option value="damaged">Rusak (Set Unavailable)</option>
                            <option value="lost">Hilang (Kurangi Total Stok)</option>
                        </select>
                        <input type="text" name="admin_note" placeholder="Catatan pengembalian (opsional)"
                               class="border-gray-300 rounded-lg text-xs">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-500">
                            Proses Pengembalian
                        </button>
                    </form>
                @endif

                {{-- Aksi Denda Belum Lunas: Tandai Lunas --}}
                @if ($rental->fine_amount > 0 && ! $rental->fine_paid)
                    <form method="POST" action="{{ route('admin.rentals.mark-fine-paid', $rental) }}" class="pt-2">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-500">
                            Verifikasi Denda Lunas
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white p-8 rounded-xl text-center text-gray-500 text-sm">
                Belum ada data peminjaman.
            </div>
        @endforelse
    </div>
</x-app-layout>
