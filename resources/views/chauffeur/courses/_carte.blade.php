@php
    $clients = $course->clients->pluck('nom');
    $date = ($course->statut === 'livree' && $course->livree_at) ? $course->livree_at : $course->date_heure;

    $barre = match (true) {
        $course->statut === 'livree' && $course->en_retard => 'bg-amber-400',
        $course->statut === 'livree' => 'bg-green-500',
        $course->en_retard => 'bg-red-500',
        $course->statut === 'en_cours' => 'bg-blue-500',
        default => 'bg-gray-300',
    };
@endphp

<a href="{{ route('chauffeur.courses.show', $course->id) }}"
   class="flex overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100 transition active:bg-gray-50">
    <span class="w-1.5 shrink-0 {{ $barre }}"></span>

    <div class="flex flex-1 items-center gap-3 p-4">
        <div class="w-12 shrink-0 text-center">
            <p class="text-xl font-semibold leading-none text-gray-900">{{ $date->format('d') }}</p>
            <p class="mt-1 text-xs uppercase text-gray-500">{{ $date->translatedFormat('M') }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $date->format('H:i') }}</p>
        </div>

        <div class="min-w-0 flex-1">
            <p class="truncate font-medium text-gray-900">{{ $course->adresse_depart }}</p>
            <p class="truncate text-sm text-gray-500">→ {{ $course->adresse_arrivee }}</p>

            @if ($clients->isNotEmpty())
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach ($clients->take(2) as $nom)
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $nom }}</span>
                    @endforeach
                    @if ($clients->count() > 2)
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">+{{ $clients->count() - 2 }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex shrink-0 flex-col items-end gap-2">
            <x-statut-badge :course="$course" />
            <x-heroicon-o-chevron-right class="h-4 w-4 text-gray-300" />
        </div>
    </div>
</a>
