<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'SepedaKuy') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <main class="guest-shell">
            <section class="guest-story">
                <a href="/" class="guest-logo">
                    <x-sepedakuy-logo class="sepedakuy-brand--large" />
                </a>
                <div class="guest-story-copy">
                    <span class="eyebrow"><span></span> TEMUKAN RUTE BARUMU</span>
                    <h1>Ride your<br><span>next story.</span></h1>
                    <p>Sepeda yang pas untuk jalan-jalan, berangkat, dan menjelajah lebih jauh.</p>
                </div>
                <div class="guest-story-foot">
                    <span>SEPEDA KUY</span>
                    <span>MOVE AT YOUR OWN PACE</span>
                </div>
            </section>
            <section class="guest-panel">
                <a href="/" class="guest-mobile-logo">
                    <x-sepedakuy-logo class="sepedakuy-brand--large" />
                </a>
                <div class="guest-card">
                    <div class="guest-card-heading">
                        <span class="eyebrow"><span></span> SEPEDA KUY ACCOUNT</span>
                        <h2>
                            @if (request()->routeIs('login'))
                                Selamat datang kembali.
                            @elseif (request()->routeIs('register'))
                                Mulai perjalananmu.
                            @elseif (request()->routeIs('password.request', 'password.reset'))
                                Atur ulang password.
                            @elseif (request()->routeIs('password.confirm'))
                                Konfirmasi identitas.
                            @else
                                Satu langkah lagi.
                            @endif
                        </h2>
                    </div>
                    {{ $slot }}
                </div>
                <p class="guest-copyright">© {{ date('Y') }} SepedaKuy · Ride your way.</p>
            </section>
        </main>
    </body>
</html>
