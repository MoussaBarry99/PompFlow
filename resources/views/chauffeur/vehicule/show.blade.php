<x-chauffeur-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Mon véhicule</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-5 px-4 py-5 sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        @if (! $vehicle)
            <div class="rounded-lg bg-white p-4 text-sm text-gray-600 shadow-sm ring-1 ring-gray-100">
                Aucun véhicule ne t'est affecté. Contacte ton responsable.
            </div>
        @else
            @php
                $statut = $vehicle->entretien_statut;
                $date = $vehicle->prochain_entretien;
                $jours = $date ? (int) today()->diffInDays($date, false) : null;
            @endphp

            {{-- Fiche du véhicule --}}
            <section class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-white">
                        <x-heroicon-o-truck class="h-7 w-7" />
                    </span>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $vehicle->immatriculation }}</p>
                        <p class="text-sm text-gray-500">{{ $vehicle->type }}</p>
                    </div>
                </div>

                <dl class="mt-4 divide-y divide-gray-100 text-sm">
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500">Immatriculation</dt>
                        <dd class="font-medium text-gray-900">{{ $vehicle->immatriculation }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500">Type</dt>
                        <dd class="font-medium text-gray-900">{{ $vehicle->type }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500">Capacité</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($vehicle->capacite, 0, ',', "\u{202F}") }} kg</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500">Chauffeur</dt>
                        <dd class="font-medium text-gray-900">{{ auth()->user()->name }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-500">Société</dt>
                        <dd class="font-medium text-gray-900">{{ auth()->user()->company?->libelle }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Entretien --}}
            <section class="rounded-lg p-4 shadow-sm ring-1
                {{ match ($statut) {
                    'en_retard' => 'bg-red-50 ring-red-200',
                    'bientot' => 'bg-amber-50 ring-amber-200',
                    default => 'bg-white ring-gray-100',
                } }}">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-wrench-screwdriver class="h-6 w-6 shrink-0
                        {{ match ($statut) {
                            'en_retard' => 'text-red-600',
                            'bientot' => 'text-amber-600',
                            'ok' => 'text-green-600',
                            default => 'text-gray-400',
                        } }}" />
                    <div class="text-sm">
                        <p class="font-semibold text-gray-900">Entretien</p>
                        @if (! $date)
                            <p class="text-gray-600">Aucun entretien planifié pour le moment.</p>
                        @else
                            <p class="text-gray-700">Prochain entretien le <span class="font-medium">{{ $date->format('d/m/Y') }}</span></p>
                            <p class="mt-0.5 font-medium
                                {{ match ($statut) { 'en_retard' => 'text-red-700', 'bientot' => 'text-amber-700', default => 'text-green-700' } }}">
                                @if ($jours < 0)
                                    En retard de {{ abs($jours) }} jour(s). Préviens ton responsable.
                                @elseif ($jours === 0)
                                    C'est aujourd'hui.
                                @elseif ($statut === 'bientot')
                                    Dans {{ $jours }} jour(s).
                                @else
                                    Dans {{ $jours }} jours, tout est à jour.
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Signalement --}}
            <section id="signaler" class="scroll-mt-20 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <h3 class="font-semibold text-gray-900">Signaler un problème</h3>

                <form method="POST" action="{{ route('chauffeur.problemes.store') }}" class="mt-3 space-y-4">
                    @csrf

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Type de problème</label>
                        <select id="type" name="type" required class="mt-1 w-full rounded-md border-gray-300 text-sm">
                            @foreach (\App\Models\VehicleIssue::TYPES as $cle => $libelle)
                                <option value="{{ $cle }}" @selected(old('type') === $cle)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-700">Gravité</span>
                        <div class="mt-1 grid grid-cols-3 gap-2">
                            @foreach (\App\Models\VehicleIssue::GRAVITES as $cle => $libelle)
                                <label class="cursor-pointer">
                                    <input type="radio" name="gravite" value="{{ $cle }}" class="peer sr-only"
                                           @checked(old('gravite', 'moyenne') === $cle)>
                                    <span class="block rounded-md border border-gray-300 px-2 py-2 text-center text-sm text-gray-700
                                                 peer-checked:border-gray-800 peer-checked:bg-gray-800 peer-checked:text-white">
                                        {{ $libelle }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Que se passe-t-il ?</label>
                        <textarea id="description" name="description" rows="4" required maxlength="1000"
                                  class="mt-1 w-full rounded-md border-gray-300 text-sm"
                                  placeholder="Ex. : voyant moteur allumé, bruit anormal à l'avant gauche…">{{ old('description') }}</textarea>
                    </div>

                    <button class="w-full rounded-md bg-red-600 px-4 py-3 font-medium text-white">
                        Envoyer au responsable
                    </button>
                </form>
            </section>

            {{-- Historique --}}
            <section class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-2 font-semibold text-gray-900">Mes derniers signalements</h3>
                <ul class="divide-y divide-gray-100">
                    @forelse ($issues as $issue)
                        <li class="flex items-start justify-between gap-3 py-2">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $issue->type_libelle }} · {{ $issue->gravite_libelle }}</p>
                                <p class="text-xs text-gray-500">{{ $issue->created_at->format('d/m/Y H:i') }} — {{ $issue->description }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs
                                {{ $issue->statut === 'resolu' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $issue->statut === 'resolu' ? 'Résolu' : 'Ouvert' }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Aucun signalement.</li>
                    @endforelse
                </ul>
            </section>
        @endif
    </div>
</x-chauffeur-layout>
