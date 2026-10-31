<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Vehicle;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Opérations';

    protected static ?string $navigationLabel = 'Équipe';

    protected static ?string $modelLabel = 'membre';

    protected static ?string $pluralModelLabel = 'membres';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
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
            ->helperText(fn (string $context): ?string => $context === 'edit'
                ? 'Laisse vide pour ne pas changer le mot de passe.'
                : null)
            ->maxLength(255),
        Forms\Components\Select::make('role')
            ->label('Rôle')
            ->options([
                'chauffeur' => 'Chauffeur',
                'employe' => 'Employé',
            ])
            ->required()
            ->live(),
        Forms\Components\Select::make('vehicule_conduit')
            ->label('Véhicule conduit')
            ->options(function ($record) {
                return Vehicle::where('company_id', auth()->user()->company_id)
                    ->where(fn ($query) => $query
                        ->whereNull('driver_id')
                        ->orWhere('driver_id', $record?->id))
                    ->orderBy('immatriculation')
                    ->pluck('immatriculation', 'id');
            })
            ->searchable()
            ->placeholder('Aucun véhicule')
            ->helperText('Seuls les véhicules sans chauffeur sont proposés.')
            ->visible(fn (Get $get): bool => $get('role') === 'chauffeur')
            ->dehydrated(false),
        Forms\Components\Select::make('vehicle_id')
            ->label('Véhicule affecté')
            ->relationship(
                'vehicle',
                'immatriculation',
                fn ($query) => $query->where('company_id', auth()->user()->company_id)
            )
            ->searchable()
            ->preload()
            ->helperText('Plusieurs employés peuvent partager le même véhicule.')
            ->visible(fn (Get $get): bool => $get('role') === 'employe')
            ->dehydrated(fn (Get $get): bool => $get('role') === 'employe'),
    ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Adresse e-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'chauffeur' => 'Chauffeur',
                        'employe' => 'Employé',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => $state === 'chauffeur' ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('vehicule')
                    ->label('Véhicule')
                    ->state(fn (User $record): ?string => $record->role === 'chauffeur'
                        ? $record->drivenVehicle?->immatriculation
                        : $record->vehicle?->immatriculation)
                    ->placeholder('Non affecté'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'chauffeur' => 'Chauffeur',
                        'employe' => 'Employé',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun membre');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->role === 'responsable';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['vehicle', 'drivenVehicle']);

        if (auth()->user()->role === 'responsable') {
            $query->where('company_id', auth()->user()->company_id)
                ->where('role', '!=', 'responsable');
        }

        return $query;
    }
}