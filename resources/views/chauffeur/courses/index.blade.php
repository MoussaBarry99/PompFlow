<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes courses</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3">
            @forelse ($courses as $course)
                <a href="{{ route('chauffeur.courses.show', $course->id) }}"
                   class="block bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $course->adresse_depart }} → {{ $course->adresse_arrivee }}
                            </p>
                            <p class="text-sm text-gray-500">
                                Départ {{ $course->date_heure->format('d/m/Y H:i') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $course->clients->pluck('nom')->join(', ') }}
                            </p>
                        </div>
                        <x-statut-badge :course="$course" />
                    </div>
                </a>
            @empty
                <p class="text-gray-500 text-center py-10">Aucune course pour le moment.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>