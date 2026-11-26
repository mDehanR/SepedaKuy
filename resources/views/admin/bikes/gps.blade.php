<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Pemantauan Armada</p>
                <h2 class="mt-1 text-xl font-bold leading-tight text-gray-900 sm:text-2xl">GPS Sepeda</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    @php
        $gpsActiveCount = $activeBikes->filter(
            fn ($bike) => $bike->latitude !== null && $bike->longitude !== null
        )->count();
    @endphp

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />

    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Ringkasan GPS">
            <div class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-blue-50 text-blue-700">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="6" cy="17" r="3" />
                        <circle cx="18" cy="17" r="3" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 17 4-8 4 8H6Zm4-8h4l4 8m-8-8-2-2" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500">Sepeda sedang dipinjam</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-gray-900">{{ $activeBikes->count() }} <span class="text-sm font-semibold text-gray-500">unit</span></p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-emerald-50 text-emerald-700">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 5.25-7.5 11.25-7.5 11.25S4.5 15.75 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        <circle cx="12" cy="10.5" r="2.25" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500">GPS aktif</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-gray-900">{{ $gpsActiveCount }} <span class="text-sm font-semibold text-gray-500">dari {{ $activeBikes->count() }} unit</span></p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:col-span-2 xl:col-span-1">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-indigo-50 text-indigo-700">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500">Pembaruan posisi</p>
                    <p class="mt-1 text-lg font-bold text-gray-900">Otomatis setiap 7 detik</p>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[minmax(19rem,0.82fr)_minmax(0,1.8fr)]">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h3 class="font-bold text-gray-900">Sepeda Dipinjam</h3>
                        <p class="mt-1 text-xs text-gray-500">Pilih unit untuk melihat lokasinya di peta.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                        {{ $activeBikes->count() }} unit
                    </span>
                </div>

                <div class="space-y-3 p-4 xl:max-h-[calc(100vh-20rem)] xl:overflow-y-auto">
                    @forelse ($activeBikes as $bike)
                        @php
                            $rental = $bike->rentals->first();
                            $hasCoordinates = $bike->latitude !== null && $bike->longitude !== null;
                        @endphp
                        <article id="bike-card-{{ $bike->id }}" class="rounded-xl border border-gray-200 bg-white p-4 transition duration-200 hover:border-indigo-200 hover:shadow-sm">
                            <button
                                type="button"
                                onclick="focusBike({{ $bike->latitude ?? 'null' }}, {{ $bike->longitude ?? 'null' }}, {{ $bike->id }})"
                                class="w-full rounded-lg text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                aria-label="Fokuskan {{ $bike->name }} di peta"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="truncate font-bold text-gray-900">{{ $bike->name }}</h4>
                                        @if ($bike->code)
                                            <p class="mt-1 text-xs font-medium text-gray-500">{{ $bike->code }}</p>
                                        @endif
                                    </div>
                                    @if ($hasCoordinates)
                                        <span class="inline-flex flex-none items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            GPS aktif
                                        </span>
                                    @else
                                        <span class="inline-flex flex-none items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Belum ada koordinat
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 rounded-lg bg-gray-50 px-3 py-2.5 text-sm">
                                    <p class="text-gray-500">Penyewa</p>
                                    <p class="mt-0.5 font-semibold text-gray-900">{{ $rental?->user?->name ?? '-' }}</p>
                                    <p class="mt-1 text-xs text-gray-600">Telepon: {{ $rental?->user?->phone ?? '-' }}</p>
                                </div>

                                @if ($hasCoordinates)
                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-gray-500">
                                        <span class="font-mono">{{ $bike->latitude }}, {{ $bike->longitude }}</span>
                                        <span>{{ $bike->gps_updated_at?->diffForHumans() ?? 'Baru saja' }}</span>
                                    </div>
                                @else
                                    <p class="mt-3 text-xs text-amber-700">Atur koordinat GPS untuk menampilkan sepeda di peta.</p>
                                @endif
                            </button>

                            <form
                                onsubmit="submitBikeGps(event, {{ $bike->id }})"
                                class="mt-4 grid grid-cols-2 gap-2 border-t border-gray-100 pt-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]"
                            >
                                <label class="sr-only" for="latitude-{{ $bike->id }}">Latitude</label>
                                <input
                                    id="latitude-{{ $bike->id }}"
                                    type="number"
                                    step="any"
                                    min="-90"
                                    max="90"
                                    name="latitude"
                                    value="{{ $bike->latitude }}"
                                    placeholder="Latitude"
                                    required
                                    class="min-w-0 rounded-lg border-gray-300 px-3 py-2 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <label class="sr-only" for="longitude-{{ $bike->id }}">Longitude</label>
                                <input
                                    id="longitude-{{ $bike->id }}"
                                    type="number"
                                    step="any"
                                    min="-180"
                                    max="180"
                                    name="longitude"
                                    value="{{ $bike->longitude }}"
                                    placeholder="Longitude"
                                    required
                                    class="min-w-0 rounded-lg border-gray-300 px-3 py-2 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <button
                                    type="submit"
                                    class="col-span-2 inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-span-1"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.25 7-11a7 7 0 1 0-14 0c0 5.75 7 11 7 11Z" />
                                        <circle cx="12" cy="10" r="2.25" />
                                    </svg>
                                    Set GPS
                                </button>
                            </form>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-10 text-center">
                            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-white text-gray-400 shadow-sm">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 5.25-7.5 11.25-7.5 11.25S4.5 15.75 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    <circle cx="12" cy="10.5" r="2.25" />
                                </svg>
                            </span>
                            <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada sepeda yang dipinjam</p>
                            <p class="mt-1 text-xs text-gray-500">Posisi sepeda akan muncul di sini saat peminjaman aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="relative isolate z-0 overflow-hidden rounded-2xl border border-gray-200 bg-white p-2 shadow-md sm:p-3">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 px-2 pt-1">
                    <div>
                        <h3 class="font-bold text-gray-900">Peta Lokasi</h3>
                        <p class="mt-1 text-xs text-gray-500">Peta OpenStreetMap · posisi diperbarui otomatis</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Live
                    </span>
                </div>
                <div id="bike-gps-map" class="z-0 h-[520px] w-full rounded-xl border border-gray-200 bg-gray-100 sm:h-[600px] xl:h-[calc(100vh-13rem)] xl:min-h-[520px] xl:max-h-[780px]"></div>
            </div>
        </section>
    </div>

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <script>
        let map;
        const markers = {};
        let currentBikes = @json($activeBikes);
        let selectedBikeId = null;
        const csrfToken = @json(csrf_token());

        document.addEventListener('DOMContentLoaded', () => {
            const firstWithCoord = currentBikes.find(bike => bike.latitude !== null && bike.longitude !== null);
            const center = firstWithCoord
                ? [Number(firstWithCoord.latitude), Number(firstWithCoord.longitude)]
                : [-7.2104, 107.9087];

            map = L.map('bike-gps-map').setView(center, 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            renderMarkers(currentBikes);
            setInterval(refreshPositions, 7000);
        });

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        function renderMarkers(bikes) {
            if (!map) return;

            const activeIds = new Set();

            bikes.forEach((bike) => {
                if (bike.latitude === null || bike.longitude === null) return;

                activeIds.add(String(bike.id));
                const latLng = [Number(bike.latitude), Number(bike.longitude)];
                const rental = bike.rentals?.[0];
                const borrower = escapeHtml(rental?.user?.name ?? 'Penyewa');
                const phone = escapeHtml(rental?.user?.phone ?? '-');
                const bikeTitle = escapeHtml(bike.name ?? 'Sepeda #' + bike.id);
                const popupHtml = `
                    <div style="font-size:13px;line-height:1.45">
                        <strong>${bikeTitle}</strong><br>
                        Penyewa: ${borrower}<br>
                        Telp: ${phone}<br>
                        Koord: ${Number(bike.latitude).toFixed(6)}, ${Number(bike.longitude).toFixed(6)}
                    </div>
                `;

                if (markers[bike.id]) {
                    markers[bike.id].setLatLng(latLng).setPopupContent(popupHtml);
                } else {
                    markers[bike.id] = L.marker(latLng)
                        .addTo(map)
                        .bindPopup(popupHtml);
                }
            });

            Object.keys(markers).forEach((id) => {
                if (!activeIds.has(id)) {
                    map.removeLayer(markers[id]);
                    delete markers[id];
                }
            });
        }

        function focusBike(lat, lng, bikeId) {
            selectedBikeId = String(bikeId);
            document.querySelectorAll('[id^="bike-card-"]').forEach((card) => {
                const isSelected = card.id === `bike-card-${selectedBikeId}`;
                card.classList.toggle('border-indigo-300', isSelected);
                card.classList.toggle('bg-indigo-50', isSelected);
                card.classList.toggle('ring-2', isSelected);
                card.classList.toggle('ring-indigo-200', isSelected);
                card.classList.toggle('border-gray-200', !isSelected);
                card.classList.toggle('bg-white', !isSelected);
            });

            if (lat === null || lng === null) return;

            map.setView([Number(lat), Number(lng)], 16, { animate: true });
            markers[bikeId]?.openPopup();
        }

        async function refreshPositions() {
            try {
                const response = await fetch(@json(route('admin.bikes.gps.positions')));
                if (!response.ok) return;
                const data = await response.json();
                currentBikes = data.bikes || [];
                renderMarkers(currentBikes);
            } catch (error) {
                console.error('Gagal memuat posisi GPS:', error);
            }
        }

        async function submitBikeGps(event, bikeId) {
            event.preventDefault();
            const formData = new FormData(event.target);

            try {
                const response = await fetch(`/admin/bikes/${bikeId}/gps`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    alert('Gagal memperbarui koordinat GPS.');
                    return;
                }

                await refreshPositions();
                const updated = currentBikes.find(bike => bike.id === bikeId);
                if (updated) {
                    focusBike(updated.latitude, updated.longitude, updated.id);
                }
            } catch (error) {
                console.error('Gagal memperbarui koordinat GPS:', error);
                alert('Terjadi kesalahan saat memperbarui koordinat GPS.');
            }
        }
    </script>
</x-app-layout>
