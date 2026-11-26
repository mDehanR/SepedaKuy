<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <a href="{{ route('admin.bikes.gps') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 5.25-7.5 11.25-7.5 11.25S4.5 15.75 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    <circle cx="12" cy="10.5" r="2.25" />
                </svg>
                GPS Sepeda
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs text-gray-500">Stok Sepeda Tersedia</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $availableStockAll }} / {{ $totalStockAll }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs text-gray-500">Menunggu Persetujuan</span>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs text-gray-500">Sedang Dipinjam (Aktif)</span>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $activeCount }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs text-gray-500">Pendapatan Sewa + Denda Lunas</span>
                <p class="text-2xl font-bold text-green-700 mt-1">
                    Rp {{ number_format($totalRentalRevenue + $totalFineRevenue, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Peminjaman Aktif & Status Keterlambatan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
            <h3 class="font-semibold text-gray-800">Peminjaman Aktif & Pengingat Waktu</h3>
            @forelse ($activeRentals as $rental)
                <div class="p-3.5 rounded-lg border border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                    <div>
                        <strong>#{{ $rental->id }} {{ $rental->user->name }}</strong> — {{ $rental->bike->name }}
                        <span class="block text-xs text-gray-500">
                            Batas kembali: {{ $rental->due_time?->format('d M Y H:i') }}
                        </span>
                    </div>
                    <span class="text-xs font-semibold {{ ($rental->reminder['state'] ?? '') === 'overdue' ? 'text-red-600' : 'text-blue-700' }}">
                        {{ $rental->reminder['message'] ?? '' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Tidak ada peminjaman aktif saat ini.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
