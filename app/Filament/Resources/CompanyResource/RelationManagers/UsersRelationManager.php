<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Filament\Actions\RenvoyerInvitationAction;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Membres de la société';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nom complet')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->label('Adresse e-mail')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Forms\Components\TextInput::make('password')
                ->label('Mot de passe')
                ->password()
                ->required(fn (string $context): bool => $context === 'create')
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->dehydrated(fn ($state) => filled($state))
                ->helperText(fn (string $context): string => $context === 'create'
                    ? 'Mot de passe provisoire : le responsable recevra un email pour choisir le sien.'
                    : 'Laisse vide pour ne pas changer le mot de passe.')
                ->maxLength(255),
            Forms\Components\Hidden::make('role')
                ->default('responsable'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Adresse e-mail'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'responsable' => 'Responsable',
                        'chauffeur' => 'Chauffeur',
                        'employe' => 'Employé',
                        default => ucfirst($state),
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Créer un responsable')
                    ->after(function (User $record): void {
                        if (! $record->envoyerInvitation()) {
                            Notification::make()
                                ->warning()
                                ->title("Compte créé, mais l'email n'a pas pu être envoyé")
                                ->body("Utilise « Renvoyer l'invitation » dans la liste.")
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                RenvoyerInvitationAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}