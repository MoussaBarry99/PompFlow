<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 shrink-0 text-danger-600" />

            <div class="flex-1">
                <p class="font-semibold text-gray-950 dark:text-white">
                    {{ $this->getTotal() }} problème(s) véhicule à traiter
                </p>

                <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                    @foreach ($this->getRecents() as $issue)
                        <li>
                            <span class="font-medium">{{ $issue->vehicle->immatriculation }}</span>
                            — {{ $issue->type_libelle }}
                            ({{ strtolower($issue->gravite_libelle) }})
                            · {{ $issue->user->name }},
                            {{ $issue->created_at->diffForHumans() }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ $this->getUrl() }}"
                   class="mt-3 inline-block text-sm font-medium text-primary-600 hover:underline">
                    Voir tous les signalements →
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
