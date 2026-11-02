<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-wrench-screwdriver"
                class="h-6 w-6 shrink-0 {{ $this->getEnRetard() > 0 ? 'text-danger-600' : 'text-warning-600' }}" />

            <div class="flex-1">
                <p class="font-semibold text-gray-950 dark:text-white">
                    {{ $this->getTotal() }} entretien(s) à prévoir
                    @if ($this->getEnRetard() > 0)
                        <span class="text-danger-600">· dont {{ $this->getEnRetard() }} en retard</span>
                    @endif
                </p>

                <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                    @foreach ($this->getVehicules() as $vehicule)
                        @php($jours = today()->diffInDays($vehicule->prochain_entretien, false))
                        <li>
                            <span class="font-medium">{{ $vehicule->immatriculation }}</span>
                            — {{ $vehicule->prochain_entretien->format('d/m/Y') }}
                            @if ($jours < 0)
                                <span class="text-danger-600">(en retard de {{ abs((int) $jours) }} j)</span>
                            @elseif ($jours == 0)
                                <span class="text-warning-600">(aujourd'hui)</span>
                            @else
                                <span class="text-warning-600">(dans {{ (int) $jours }} j)</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <a href="{{ $this->getUrl() }}"
                   class="mt-3 inline-block text-sm font-medium text-primary-600 hover:underline">
                    Voir les véhicules concernés →
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
