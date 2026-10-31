@php
    $clients = $course->clients->pluck('nom')->join(', ');
@endphp

<a href="{{ route('chauffeur.courses.show', $course->id) }}"
   class="block rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-100 active:bg-gray-50">
    <div class="flex items-start justify-between gap-3">
        <p class="font-medium text-gray-900">
            {{ $course->adresse_depart }} → {{ $course->adresse_arrivee }}
        </p>
        <x-statut-badge :course="$course" />
    </div>

    <p class="mt-1 text-sm text-gray-600">
        @if ($course->statut === 'livree' && $course->livree_at)
            Livrée le {{ $course->livree_at->format('d/m/Y à H:i') }}
        @else
            Départ le {{ $course->date_heure->format('d/m/Y à H:i') }}
        @endif
    </p>

    @if ($clients)
        <p class="mt-1 text-sm text-gray-500">{{ $clients }}</p>
    @endif
</a>
