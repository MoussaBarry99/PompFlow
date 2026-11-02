<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Opérations';

    protected static ?string $navigationLabel = 'Véhicules';

    protected static ?string $modelLabel = 'véhicule';

    protected static ?string $pluralModelLabel = 'véhicules';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('immatriculation')
                ->label('Immatriculation')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Forms\Components\TextInput::make('type')
                ->label('Type de véhicule')
                ->maxLength(255),
            Forms\Components\TextInput::make('capacite')
                ->label('Capacité de charge')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->suffix('tonnes'),
            Forms\Components\Select::make('statut_entretien')
                ->label('État du véhicule')
                ->options([
                    'ok' => 'En service',
                    'en_entretien' => 'En entretien',
                    'hors_service' => 'Hors service',
                ])
                ->default('ok')
                ->required()
                ->helperText('Seuls les véhicules en service peuvent être affectés à une course.'),
            Forms\Components\Select::make('driver_id')
                ->label('Chauffeur')
                ->relationship('driver', 'name', function ($query, $record) {
                    return $query
                        ->where('company_id', auth()->user()->company_id)
                        ->where('role', 'chauffeur')
                        ->where(function ($q) use ($record) {
                            $q->whereDoesntHave('drivenVehicle')
                                ->orWhere('id', $record?->driver_id);
                        });
                })
                ->searchable()
                ->preload()
                ->placeholder('Aucun chauffeur')
                ->helperText(''),
            Forms\Components\DatePicker::make('prochain_entretien')
                ->label('Prochain entretien')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection()
                ->minDate(today())
                ->helperText('mettre à jour la date après chaque entretien.'),
        
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('immatriculation')
                    ->label('Immatriculation')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacite')
                    ->label('Capacité')
                    ->formatStateUsing(fn ($state) => $state
                        ? number_format((float) $state, 0, ',', "\u{00A0}") . "\u{00A0}tonnes"
                        : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut_entretien')
                    ->label('État')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ok' => 'En service',
                        'en_entretien' => 'En entretien',
                        'hors_service' => 'Hors service',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ok' => 'success',
                        'en_entretien' => 'warning',
                        'hors_service' => 'danger',
                        default => 'gray',
                    }),
                    Tables\Columns\TextColumn::make('prochain_entretien')
                    ->label('Prochain entretien')
                    ->date('d/m/Y')
                    ->placeholder('Non planifié')
                    ->sortable()
                    ->badge()
                    ->color(fn (Vehicle $record): string => match ($record->entretien_statut) {
                        'en_retard' => 'danger',
                        'bientot' => 'warning',
                        'ok' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Chauffeur')
                    ->placeholder('Non affecté')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employees_count')
                    ->label('Employés')
                    ->counts('employees'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('immatriculation')
            ->filters([
                Tables\Filters\SelectFilter::make('statut_entretien')
                    ->label('État')
                    ->options([
                        'ok' => 'En service',
                        'en_entretien' => 'En entretien',
                        'hors_service' => 'Hors service',
                    ]),
                    Tables\Filters\Filter::make('entretien_a_prevoir')
                        ->label('Entretien à prévoir')
                        ->toggle()
                        ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query
                            ->whereNotNull('prochain_entretien')
                            ->whereDate('prochain_entretien', '<=', today()->addDays(14))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun véhicule');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
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
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id);
    }
}