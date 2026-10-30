@props(['course'])

@php
    $classes = match ($course->statut_affiche) {
        'Livrée' => 'bg-green-100 text-green-800',
        'Livrée en retard' => 'bg-orange-100 text-orange-800',
        'En retard' => 'bg-red-100 text-red-800',
        'En cours' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $classes }}">
    {{ $course->statut_affiche }}
</span>