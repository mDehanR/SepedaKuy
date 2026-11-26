@php
    $isAdmin = Auth::user()->role === 'admin';
    $navigationItems = $isAdmin
        ? [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Data Sepeda', 'route' => 'admin.bikes.index', 'active' => 'admin.bikes.*'],
            ['label' => 'GPS Sepeda', 'route' => 'admin.bikes.gps', 'active' => 'admin.bikes.gps'],
            ['label' => 'Peminjaman', 'route' => 'admin.rentals.index', 'active' => 'admin.rentals.*'],
            ['label' => 'Profil', 'route' => 'profile.edit', 'active' => 'profile.*'],
        ]
        : [
            ['label' => 'Katalog Sepeda', 'route' => 'user.dashboard', 'active' => 'user.dashboard'],
            ['label' => 'Peminjaman Saya', 'route' => 'user.rentals.index', 'active' => 'user.rentals.*'],
            ['label' => 'Profil', 'route' => 'profile.edit', 'active' => 'profile.*'],
        ];
@endphp

<aside class="app-sidebar" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
    <div class="sidebar-brand">
        <a href="{{ route($isAdmin ? 'admin.dashboard' : 'user.dashboard') }}" class="sidebar-brand-link">
            <x-sepedakuy-logo />
        </a>
        <button type="button" class="sidebar-close md:hidden" @click="sidebarOpen = false" aria-label="Tutup navigasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <div class="sidebar-section-label" x-show="!sidebarCollapsed">MENU UTAMA</div>
    <nav class="sidebar-nav" aria-label="Navigasi utama">
        @foreach ($navigationItems as $item)
            <a href="{{ route($item['route']) }}"
               title="{{ $item['label'] }}"
               @class([
                   'sidebar-link',
                   'sidebar-link-active' => request()->routeIs($item['active']),
               ])
               @click="sidebarOpen = false">
                @if ($item['route'] === 'admin.bikes.gps')
                    <svg class="h-5 w-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 5.25-7.5 11.25-7.5 11.25S4.5 15.75 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        <circle cx="12" cy="10.5" r="2.25" />
                    </svg>
                @else
                    <span class="sidebar-link-mark" aria-hidden="true"></span>
                @endif
                <span class="sidebar-link-label" x-show="!sidebarCollapsed">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-bottom">
        <div class="sidebar-member" x-show="!sidebarCollapsed">
            <span class="member-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            <span class="member-details">
                <span class="member-name">{{ Auth::user()->name }}</span>
                <span class="member-role">{{ $isAdmin ? 'Administrator' : 'Member SepedaKuy' }}</span>
            </span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout" aria-label="Keluar dari akun">
                <span class="sidebar-link-mark" aria-hidden="true"></span>
                <span class="sidebar-link-label" x-show="!sidebarCollapsed">{{ __('Log Out') }}</span>
            </button>
        </form>
        <button type="button" class="sidebar-collapse hidden md:flex" @click="sidebarCollapsed = !sidebarCollapsed">
            <span x-text="sidebarCollapsed ? '›' : '‹'" aria-hidden="true"></span>
            <span x-show="!sidebarCollapsed">Perkecil menu</span>
        </button>
    </div>
</aside>
