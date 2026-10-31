<x-app-layout>
    <style>[x-cloak]{display:none !important}</style>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes courses</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5"
             x-data="{ onglet: '{{ $enCours->isNotEmpty() ? 'encours' : 'avenir' }}' }">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            {{-- Véhicule + signalement --}}
            <div class="flex items-center justify-between gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Bonjour {{ auth()->user()->name }} · {{ auth()->user()->company?->libelle }}</p>
                    @if ($vehicle)
                        <p class="font-semibold text-gray-900">
                            {{ $vehicle->immatriculation }}
                            <span class="font-normal text-gray-500">· {{ $vehicle->type }}</span>
                        </p>
                    @else
                        <p class="font-medium text-amber-700">Aucun véhicule affecté</p>
                    @endif
                </div>

                @if ($vehicle)
                    <a href="{{ route('chauffeur.problemes.create') }}"
                       class="shrink-0 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700">
                        Signaler un problème
                    </a>
                @endif
            </div>

            {{-- Onglets --}}
            <div class="grid grid-cols-3 gap-2">
                @foreach ([
                    'avenir' => ['À venir', $aVenir->count()],
                    'encours' => ['En cours', $enCours->count()],
                    'livrees' => ['Livrées', $livrees->count()],
                ] as $cle => [$libelle, $nombre])
                    <button type="button" @click="onglet = '{{ $cle }}'"
                            :class="onglet === '{{ $cle }}'
                                ? 'bg-gray-800 text-white'
                                : 'bg-white text-gray-700 ring-1 ring-gray-200'"
                            class="rounded-lg px-2 py-3 text-center">
                        <span class="block text-xl font-semibold leading-none">{{ $nombre }}</span>
                        <span class="mt-1 block text-xs">{{ $libelle }}</span>
                    </button>
                @endforeach
            </div>

            {{-- À venir --}}
            <div x-show="onglet === 'avenir'" class="space-y-3">
                @forelse ($aVenir as $course)
                    @include('chauffeur.courses._carte', ['course' => $course])
                @empty
                    <p class="py-8 text-center text-sm text-gray-500">Aucune course à venir.</p>
                @endforelse
            </div>

            {{-- En cours --}}
            <div x-show="onglet === 'encours'" x-cloak class="space-y-3">
                @forelse ($enCours as $course)
                    @include('chauffeur.courses._carte', ['course' => $course])
                @empty
                    <p class="py-8 text-center text-sm text-gray-500">Aucune livraison en cours.</p>
                @endforelse
            </div>

            {{-- Livrées --}}
            <div x-show="onglet === 'livrees'" x-cloak class="space-y-3">
                @forelse ($livrees as $course)
                    @include('chauffeur.courses._carte', ['course' => $course])
                @empty
                    <p class="py-8 text-center text-sm text-gray-500">Aucune course livrée pour le moment.</p>
                @endforelse
                @if ($livrees->isNotEmpty())
                    <p class="text-center text-xs text-gray-400">Les 30 dernières livraisons.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
