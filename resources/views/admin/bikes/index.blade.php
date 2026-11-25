<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manajemen Stok & Harga Sepeda
            </h2>
            <a href="{{ route('admin.bikes.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-500">
                + Tambah Sepeda
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if (session('success'))
            <div class="p-4 rounded-lg bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-lg bg-red-100 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama & Tipe</th>
                        <th class="px-4 py-3">Tarif / Jam</th>
                        <th class="px-4 py-3">Denda / 10 Menit</th>
                        <th class="px-4 py-3">Stok (Tersedia / Total)</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($bikes as $bike)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $bike->code }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $bike->name }}</div>
                                <div class="text-xs text-gray-500">{{ $bike->type }}</div>
                            </td>
                            <td class="px-4 py-3">Rp {{ number_format($bike->price_per_hour, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">Rp {{ number_format((int) round($bike->price_per_hour * 0.10), 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $bike->stock_available }} / {{ $bike->stock_total }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 text-xs rounded-full {{ $bike->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $bike->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.bikes.edit', $bike) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.bikes.destroy', $bike) }}" class="inline"
                                      onsubmit="return confirm('Hapus sepeda ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
