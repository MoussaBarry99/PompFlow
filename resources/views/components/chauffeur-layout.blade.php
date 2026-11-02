@props(['header' => null])

@php
    $utilisateur = auth()->user();
    $initiales = collect(explode(' ', trim($utilisateur->name)))
        ->filter()
        ->map(fn ($partie) => mb_strtoupper(mb_substr($partie, 0, 1)))
        ->take(2)
        ->join('');

    $onglets = [
        ['route' => 'chauffeur.courses.index', 'match' => 'chauffeur.courses.*', 'label' => 'Courses', 'icon' => 'heroicon-o-clipboard-document-list'],
        ['route' => 'chauffeur.vehicule', 'match' => 'chauffeur.vehicule*', 'label' => 'Mon véhicule', 'icon' => 'heroicon-o-truck'],
        ['route' => 'profile.edit', 'match' => 'profile.*', 'label' => 'Profil', 'icon' => 'heroicon-o-user-circle'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
    <title>{{ config('app.name', 'PompFlow') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none !important}</style>
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">

    {{-- En-tête + navigation --}}
    <header class="sticky top-0 z-30 border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500 text-white">
                    <x-heroicon-o-truck class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="font-semibold">PompFlow</p>
                    <p class="text-xs text-gray-500">{{ $utilisateur->company?->libelle }}</p>
                </div>
            </div>

            <div x-data="{ ouvert: false }" class="relative">
                <button type="button" @click="ouvert = !ouvert" @click.outside="ouvert = false"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-800 text-sm font-semibold text-white">
                    {{ $initiales }}
                </button>

                <div x-show="ouvert" x-cloak
                     class="absolute right-0 mt-2 w-56 rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-200">
                    <p class="truncate px-4 py-2 text-xs text-gray-500">{{ $utilisateur->email }}</p>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Mon profil et mot de passe
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <nav class="mx-auto grid max-w-3xl grid-cols-3 px-2 sm:px-6 lg:px-8">
            @foreach ($onglets as $onglet)
                @php($actif = request()->routeIs($onglet['match']))
                <a href="{{ route($onglet['route']) }}"
                   class="flex items-center justify-center gap-1.5 border-b-2 py-2.5 text-sm
                          {{ $actif ? 'border-amber-500 font-medium text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <x-dynamic-component :component="$onglet['icon']" class="h-5 w-5" />
                    {{ $onglet['label'] }}
                </a>
            @endforeach
        </nav>
    </header>

    @if ($header)
        <div class="mx-auto max-w-3xl px-4 pt-5 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    @endif

    <main class="pb-10">
        {{ $slot }}
    </main>
</body>
</html>