<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f5f6f7">
        <title>{{ config('app.name', 'SepedaKuy') }} — Ride your way</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <main class="landing-shell">
            <header class="landing-header">
                <a href="/" class="landing-brand"><x-sepedakuy-logo /></a>
                <div class="landing-actions">
                    @if (Route::has('login'))
                        @auth
                            <a class="landing-login" href="{{ route('dashboard') }}">Dashboard</a>
                        @else
                            <a class="landing-login" href="{{ route('login') }}">Masuk</a>
                            @if (Route::has('register'))
                                <a class="landing-button" href="{{ route('register') }}">Gabung Sekarang</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </header>

            <section class="landing-hero" id="about">
                <div class="landing-copy">
                    <span class="eyebrow"><span></span> YOUR RIDE STARTS HERE</span>
                    <h1>Find your<br>next <span>ride.</span></h1>
                    <p>Rute baru selalu menunggu. Temukan sepeda yang cocok dengan ritmemu dan nikmati perjalanan dengan cara sendiri.</p>
                    <div class="landing-hero-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="landing-button">Lihat Katalog <span aria-hidden="true">→</span></a>
                        @else
                            <a href="{{ route('login') }}" class="landing-button">Mulai Jelajah <span aria-hidden="true">→</span></a>
                        @endauth
                        <span class="landing-note">Pilih. Gowes. Ulangi.</span>
                    </div>
                </div>
                <div class="landing-art" aria-hidden="true">
                    <div class="landing-art-circle"></div>
                    <svg viewBox="0 0 580 390" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M75 310h430" stroke="#D3D5D8" stroke-width="2"/>
                        <circle cx="185" cy="252" r="80" stroke="#17191D" stroke-width="9"/>
                        <circle cx="423" cy="252" r="80" stroke="#17191D" stroke-width="9"/>
                        <path d="m185 252 92-132 83 132H185Zm92-132 77 9m-64 123 67-132m-67 132h-105" stroke="#E11B22" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m266 120-29-45m0 0 57-8m-57 8-42 73m88-91 46-9" stroke="#17191D" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="344" cy="42" r="26" fill="#E11B22"/>
                        <path d="m315 72 69 34 49-7m-49 7-39 47" stroke="#17191D" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M58 144h61m-81 29h54m-31 29h36" stroke="#E11B22" stroke-width="7" stroke-linecap="round" opacity=".8"/>
                        <ellipse cx="303" cy="347" rx="170" ry="15" fill="#17191D" fill-opacity=".08"/>
                    </svg>
                    <span class="landing-art-label">RIDE YOUR WAY · EST. SEPEDA KUY</span>
                </div>
            </section>

            <section class="landing-strip" id="lineup">
                <div><span>01</span><strong>Temukan favoritmu</strong><small>Jelajahi berbagai kategori sepeda.</small></div>
                <div><span>02</span><strong>Pesan dengan mudah</strong><small>Ajukan sewa langsung dari katalog.</small></div>
                <div><span>03</span><strong>Mulai perjalanan</strong><small>Nikmati rute dengan ritmemu.</small></div>
            </section>
            <footer class="landing-footer">
                <span>© {{ date('Y') }} SEPEDA KUY</span>
                <span>Ride your way.</span>
            </footer>
        </main>
    </body>
</html>
