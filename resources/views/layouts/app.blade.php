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
        <div x-data="{ sidebarOpen: false, sidebarCollapsed: false }" class="app-shell min-h-screen">
            <div x-cloak x-show="sidebarOpen" x-transition.opacity
                 @click="sidebarOpen = false"
                 class="fixed inset-0 z-30 bg-gray-950/50 md:hidden"
                 aria-hidden="true"></div>

            @include('layouts.navigation')

            <div class="app-main min-w-0 flex-1">
                <header class="page-topbar">
                    <button type="button" @click="sidebarOpen = !sidebarOpen"
                            class="sidebar-toggle md:hidden" aria-label="Buka navigasi">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="page-breadcrumb">
                        <span class="breadcrumb-root">SEPEDA KUY</span>
                        <span aria-hidden="true">/</span>
                        <span>{{ Auth::user()->role === 'admin' ? 'ADMIN' : 'MEMBER' }}</span>
                    </div>
                    <div class="topbar-user">
                        <span class="topbar-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                    </div>
                </header>

                @isset($header)
                    <header class="page-heading">
                        <div class="page-heading-inner">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="page-content">
                    {{ $slot }}
                </main>
                <footer class="app-footer">
                    <span>SEPEDA KUY</span>
                    <span>Ride your next route.</span>
                </footer>
            </div>
        </div>
    </body>
</html>
