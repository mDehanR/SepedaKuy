<x-app-layout>
    <x-slot name="header">
        <h2>Lineup SepedaKuy</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        @if ($activeRentals->isNotEmpty())
            <section class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <span class="eyebrow"><span></span> RENTAL AKTIF</span>
                    <h3 class="mt-2 text-lg font-extrabold tracking-tight text-gray-900">Status & Pengingat Waktu</h3>
                </div>
                @foreach ($activeRentals as $rental)
                    @php
                        $state = $rental->reminder['state'] ?? 'normal';
                        $boxClass = match ($state) {
                            'overdue' => 'bg-red-50 border-red-300 text-red-900',
                            'warning' => 'bg-yellow-50 border-yellow-300 text-yellow-900',
                            default => 'bg-blue-50 border-blue-200 text-blue-900',
                        };
                    @endphp
                    <div class="flex flex-col gap-2 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between {{ $boxClass }}">
                        <div>
                            <p class="font-semibold">#{{ $rental->id }} — {{ $rental->bike->name }} ({{ $rental->bike->type }})</p>
                            <p class="mt-0.5 text-sm">
                                Status: <span class="font-semibold uppercase">{{ $rental->status }}</span>
                                @if ($rental->due_time)
                                    | Batas Kembali: <strong>{{ $rental->due_time->format('d M Y H:i') }}</strong>
                                @endif
                            </p>
                            @if ($rental->status === 'active')
                                <p class="mt-1 text-sm font-medium">{{ $rental->reminder['message'] ?? '' }}</p>
                            @endif
                        </div>
                        <a href="{{ route('user.rentals.index') }}" class="self-start text-sm font-semibold underline sm:self-center">Lihat Detail</a>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="catalog-intro">
            <span class="eyebrow"><span></span> TEMUKAN SEPEDA YANG TEPAT</span>
            <h1>Explore our <span>lineup.</span></h1>
            <p>Jelajahi koleksi sepeda pilihan untuk setiap perjalanan. Pilih kategori, temukan favoritmu, dan mulai gowes.</p>
        </section>

        <div class="catalog-toolbar">
            <nav class="catalog-category-tabs" aria-label="Filter kategori sepeda">
                <a href="{{ route('user.dashboard', array_filter(['search' => request('search')])) }}"
                   @class(['active' => ! request('type')])>All</a>
                @foreach ($bikeTypes as $type)
                    <a href="{{ route('user.dashboard', array_filter(['search' => request('search'), 'type' => $type])) }}"
                       @class(['active' => request('type') === $type])>{{ $type }}</a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('user.dashboard') }}" class="catalog-search">
                @if (request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Cari sepeda atau kode..." aria-label="Cari sepeda">
                <button type="submit">Cari</button>
            </form>
        </div>

        <div class="bike-grid">
            @forelse ($bikes as $bike)
                @php
                    $finePerTenMin = (int) round($bike->price_per_hour * 0.10);
                @endphp
                <article class="bike-card">
                    <div class="bike-visual">
                        <span class="bike-tag">{{ $bike->type }}</span>
                        @if ($bike->image)
                            <img src="{{ asset('storage/' . $bike->image) }}" alt="{{ $bike->name }}" class="bike-image">
                        @else
                            <div class="bike-placeholder">{{ $bike->type }} · {{ $bike->code }}</div>
                        @endif
                    </div>
                    <div class="bike-details">
                        <span class="bike-code">MODEL {{ $bike->code }}</span>
                        <h3>{{ $bike->name }}</h3>
                        <p class="bike-description">{{ $bike->description }}</p>
                        <div class="bike-price-row">
                            <p class="bike-price">Rp {{ number_format($bike->price_per_hour, 0, ',', '.') }} <small>/ jam</small></p>
                            <p @class(['bike-stock', 'out' => $bike->stock_available <= 0])>
                                {{ $bike->stock_available > 0 ? 'Stok ' . $bike->stock_available . ' tersedia' : 'Stok habis' }}
                            </p>
                        </div>
                        <p class="bike-fine">Denda keterlambatan Rp {{ number_format($finePerTenMin, 0, ',', '.') }} / 10 menit</p>
                        @if ($bike->stock_available > 0)
                            <a href="{{ route('user.rentals.create', $bike) }}" class="bike-cta">Pilih Sepeda</a>
                        @else
                            <button type="button" disabled class="bike-cta">Stok Habis</button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-xl border border-gray-200 bg-white p-10 text-center">
                    <p class="text-lg font-bold text-gray-800">Belum ada sepeda yang cocok.</p>
                    <p class="mt-1 text-sm text-gray-500">Coba kategori atau kata kunci pencarian lain.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
