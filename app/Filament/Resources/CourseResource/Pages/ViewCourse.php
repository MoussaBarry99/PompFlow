<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rouvrir')
                ->label('Rouvrir la course')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Rouvrir cette course ?')
                ->modalDescription('La course repasse en cours et redevient modifiable. Le chauffeur pourra déclarer des dépenses, puis valider à nouveau la livraison.')
                ->visible(fn (): bool => $this->record->statut === 'livree'
                    && auth()->user()->role === 'responsable')
                ->action(function () {
                    $this->record->rouvrir();

                    Notification::make()
                        ->title('Course rouverte')
                        ->success()
                        ->send();

                    $this->redirect(CourseResource::getUrl('edit', ['record' => $this->record]));
                }),
        ];
    }
}