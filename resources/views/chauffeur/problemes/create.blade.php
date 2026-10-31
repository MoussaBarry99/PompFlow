<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Signaler un problème</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            @if (! $vehicle)
                <div class="rounded-lg bg-white p-4 text-sm text-gray-600 shadow-sm">
                    Aucun véhicule ne t'est affecté. Contacte ton responsable.
                </div>
            @else
                <form method="POST" action="{{ route('chauffeur.problemes.store') }}"
                      class="space-y-4 rounded-lg bg-white p-4 shadow-sm">
                    @csrf

                    <p class="text-sm text-gray-600">
                        Véhicule : <span class="font-medium text-gray-900">{{ $vehicle->immatriculation }}</span>
                    </p>

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

                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <h3 class="mb-3 font-medium text-gray-900">Derniers signalements</h3>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($issues as $issue)
                            <li class="flex items-start justify-between gap-3 py-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ $issue->type_libelle }} · {{ $issue->gravite_libelle }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $issue->created_at->format('d/m/Y H:i') }} — {{ $issue->description }}
                                    </p>
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
                </div>
            @endif

            <a href="{{ route('chauffeur.courses.index') }}"
               class="block text-center text-sm text-gray-600 hover:underline">← Retour à mes courses</a>
        </div>
    </div>
</x-app-layout>
