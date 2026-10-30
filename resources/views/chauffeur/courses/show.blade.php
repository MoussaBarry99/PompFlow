<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Course du {{ $course->date_heure->format('d/m/Y') }}
            </h2>
            <x-statut-badge :course="$course" />
        </div>
    </x-slot>

    @php
        $isDriver = auth()->user()->role === 'chauffeur';
        $isOpen = in_array($course->statut, ['planifiee', 'en_cours'], true);
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-4 space-y-1">
                <p class="font-medium text-gray-900">
                    {{ $course->adresse_depart }} → {{ $course->adresse_arrivee }}
                </p>
                <p class="text-sm text-gray-500">
                    Départ prévu : {{ $course->date_heure->format('d/m/Y H:i') }}
                </p>
                <p class="text-sm text-gray-500">
                    Livraison prévue : {{ $course->livraison_prevue_at?->format('d/m/Y H:i') ?? '—' }}
                </p>
                @if ($course->livree_at)
                    <p class="text-sm text-gray-500">
                        Livrée le : {{ $course->livree_at->format('d/m/Y H:i') }}
                    </p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="font-medium text-gray-900 mb-3">À charger</h3>
                <ul class="divide-y divide-gray-100">
                    @foreach ($course->courseClients as $cc)
                        <li class="py-2">
                            <p class="font-medium text-gray-800">{{ $cc->client->nom }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $cc->type_marchandise ?? 'Marchandise non précisée' }}
                                @if ($cc->poids_kg) · {{ (float) $cc->poids_kg }} kg @endif
                            </p>
                            @if ($cc->description_marchandise)
                                <p class="text-sm text-gray-500">{{ $cc->description_marchandise }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="font-medium text-gray-900 mb-3">Dépenses</h3>

                <ul class="divide-y divide-gray-100">
                    @forelse ($course->expenses as $expense)
                        <li class="py-2 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ ucfirst($expense->type) }} · {{ number_format((float) $expense->montant, 2, ',', ' ') }} €
                                </p>
                                @if ($expense->description)
                                    <p class="text-xs text-gray-500">{{ $expense->description }}</p>
                                @endif
                            </div>
                            @if ($isDriver && $isOpen)
                                <form method="POST"
                                      action="{{ route('chauffeur.courses.expenses.destroy', [$course->id, $expense->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline">Supprimer</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Aucune dépense déclarée.</li>
                    @endforelse
                </ul>

                @if ($isDriver && $isOpen)
                    <form method="POST"
                          action="{{ route('chauffeur.courses.expenses.store', $course->id) }}"
                          class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @csrf
                        <select name="type" required class="rounded-md border-gray-300 text-sm">
                            <option value="carburant">Carburant</option>
                            <option value="peage">Péage</option>
                            <option value="repas">Repas</option>
                            <option value="autre">Autre</option>
                        </select>
                        <input type="number" name="montant" step="0.01" min="0.01" required
                               placeholder="Montant (€)" class="rounded-md border-gray-300 text-sm">
                        <input type="text" name="description" maxlength="255"
                               placeholder="Détail (optionnel)" class="rounded-md border-gray-300 text-sm">
                        <button class="sm:col-span-3 rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white">
                            Ajouter la dépense
                        </button>
                    </form>
                @endif
            </div>

            @if ($isDriver && $isOpen)
                <div class="space-y-3">
                    @if ($course->statut === 'planifiee')
                        <form method="POST" action="{{ route('chauffeur.courses.start', $course->id) }}">
                            @csrf
                            <button class="w-full rounded-md bg-blue-600 px-4 py-3 font-medium text-white">
                                Démarrer la course
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('chauffeur.courses.deliver', $course->id) }}"
                          onsubmit="return confirm('Valider la livraison ? Cette action est définitive.');">
                        @csrf
                        <button class="w-full rounded-md bg-green-600 px-4 py-3 font-medium text-white">
                            Valider la livraison
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 text-center">
                        Déclare toutes tes dépenses avant de valider : après validation, la course est verrouillée.
                    </p>
                </div>
            @endif

            <a href="{{ route('chauffeur.courses.index') }}"
               class="block text-center text-sm text-gray-600 hover:underline">← Retour à mes courses</a>
        </div>
    </div>
</x-app-layout>