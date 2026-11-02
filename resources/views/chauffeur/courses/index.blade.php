<x-chauffeur-layout>
    @php
        $prochaine = $enCours->first() ?? $aVenir->first();
        $ongletDefaut = $enCours->isNotEmpty() ? 'encours' : ($aVenir->isNotEmpty() ? 'avenir' : 'livrees');
        $prenom = explode(' ', trim(auth()->user()->name))[0];
    @endphp

    <div class="mx-auto max-w-xl space-y-5 px-4 py-5" x-data="{ onglet: '{{ $ongletDefaut }}' }">

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        {{-- Accueil --}}
        <div>
            <h1 class="text-2xl font-semibold">Bonjour {{ $prenom }}</h1>
            <p class="text-sm text-gray-500">{{ now()->translatedFormat('l j F') }}</p>
        </div>

        {{-- Véhicule --}}
        @if ($vehicle)
            <div class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <x-heroicon-o-truck class="h-6 w-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900">{{ $vehicle->immatriculation }}</p>
                    <p class="truncate text-sm text-gray-500">{{ $vehicle->type }}</p>
                </div>
                <a href="{{ route('chauffeur.vehicule') }}"
                   class="shrink-0 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700">
                    Signaler un problème
                </a>
            </div>
        @else
            <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-200">
                Aucun véhicule ne t'est affecté pour le moment. Contacte ton responsable.
            </div>
        @endif

        {{-- Course à la une --}}
        @if ($prochaine)
            @php $enRoute = $prochaine->statut === 'en_cours'; @endphp
            <section>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ $enRoute ? 'Course en cours' : 'Prochaine course' }}
                </p>

                <a href="{{ route('chauffeur.courses.show', $prochaine->id) }}"
                   class="mt-2 block rounded-2xl p-5 text-white shadow-sm {{ $enRoute ? 'bg-blue-600' : 'bg-gray-800' }}">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm opacity-80">{{ $prochaine->date_heure->translatedFormat('l j F · H:i') }}</p>
                        @if ($prochaine->en_retard)
                            <span class="rounded-full bg-red-500 px-2 py-0.5 text-xs font-medium">En retard</span>
                        @endif
                    </div>

                    <p class="mt-3 text-lg font-semibold">{{ $prochaine->adresse_depart }}</p>
                    <p class="text-lg font-semibold opacity-90">→ {{ $prochaine->adresse_arrivee }}</p>

                    @if ($prochaine->clients->isNotEmpty())
                        <p class="mt-2 text-sm opacity-80">{{ $prochaine->clients->pluck('nom')->join(', ') }}</p>
                    @endif

                    <span class="mt-4 flex w-full items-center justify-center rounded-lg bg-white px-4 py-3 font-medium text-gray-900">
                        {{ $enRoute ? 'Continuer la course' : 'Voir la course' }}
                    </span>
                </a>
            </section>
        @endif

        {{-- Onglets --}}
        <div class="grid grid-cols-3 rounded-xl bg-gray-200/70 p-1 text-sm">
            @foreach ([
                'avenir' => ['À venir', $aVenir->count()],
                'encours' => ['En cours', $enCours->count()],
                'livrees' => ['Livrées', $livrees->count()],
            ] as $cle => [$libelle, $nombre])
                <button type="button" @click="onglet = '{{ $cle }}'"
                        :class="onglet === '{{ $cle }}' ? 'bg-white font-medium text-gray-900 shadow-sm' : 'text-gray-500'"
                        class="rounded-lg py-2">
                    {{ $libelle }} <span class="text-xs text-gray-400">({{ $nombre }})</span>
                </button>
            @endforeach
        </div>

        {{-- À venir --}}
        <div x-show="onglet === 'avenir'" class="space-y-3">
            @forelse ($aVenir as $course)
                @include('chauffeur.courses._carte', ['course' => $course])
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-white/60 px-4 py-10 text-center">
                    <x-heroicon-o-inbox class="mx-auto h-8 w-8 text-gray-300" />
                    <p class="mt-2 text-sm text-gray-500">Aucune course à venir.</p>
                </div>
            @endforelse
        </div>

        {{-- En cours --}}
        <div x-show="onglet === 'encours'" x-cloak class="space-y-3">
            @forelse ($enCours as $course)
                @include('chauffeur.courses._carte', ['course' => $course])
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-white/60 px-4 py-10 text-center">
                    <x-heroicon-o-truck class="mx-auto h-8 w-8 text-gray-300" />
                    <p class="mt-2 text-sm text-gray-500">Aucune livraison en cours.</p>
                </div>
            @endforelse
        </div>

        {{-- Livrées --}}
        <div x-show="onglet === 'livrees'" x-cloak class="space-y-3">
            @forelse ($livrees as $course)
                @include('chauffeur.courses._carte', ['course' => $course])
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-white/60 px-4 py-10 text-center">
                    <x-heroicon-o-check-circle class="mx-auto h-8 w-8 text-gray-300" />
                    <p class="mt-2 text-sm text-gray-500">Aucune course livrée pour le moment.</p>
                </div>
            @endforelse

            @if ($livrees->isNotEmpty())
                <p class="text-center text-xs text-gray-400">Les 30 dernières livraisons.</p>
            @endif
        </div>
    </div>
</x-chauffeur-layout>