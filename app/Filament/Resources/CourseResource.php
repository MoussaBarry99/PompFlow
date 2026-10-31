<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Client;
use App\Models\Course;
use App\Support\Money;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Opérations';

    protected static ?string $navigationLabel = 'Courses';

    protected static ?string $modelLabel = 'course';

    protected static ?string $pluralModelLabel = 'courses';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $companyId = auth()->user()->company_id;

        return $form->schema([
            Forms\Components\Select::make('vehicle_id')
                ->label('Véhicule')
                ->relationship('vehicle', 'immatriculation', fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('statut_entretien', 'ok'))
                ->searchable()
                ->preload()
                ->rules([
                    fn (Get $get, $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                        if (! $value || ! $get('date_heure') || ! $get('livraison_prevue_at')) {
                            return;
                        }

                        $conflit = Course::chevauchant(
                            (int) $value,
                            Carbon::parse($get('date_heure')),
                            Carbon::parse($get('livraison_prevue_at')),
                            $record?->id,
                        )->first();

                        if ($conflit) {
                            $fail(
                                'Ce véhicule est déjà pris du '
                                . $conflit->date_heure->format('d/m H:i')
                                . ' au '
                                . $conflit->fin_effective->format('d/m H:i')
                                . ' (' . $conflit->adresse_depart . ' → ' . $conflit->adresse_arrivee . ').'
                            );
                        }
                    },
                ]),
            Forms\Components\TextInput::make('adresse_depart')
                ->label('Adresse de départ')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('adresse_arrivee')
                ->label('Point de dépôt (arrivée)')
                ->required()
                ->maxLength(255),
            Forms\Components\DateTimePicker::make('date_heure')
                ->label('Départ prévu')
                ->required(),
            Forms\Components\DateTimePicker::make('livraison_prevue_at')
                ->label('Livraison prévue')
                ->required()
                ->rules([
                    fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                        if ($value && $get('date_heure') && Carbon::parse($value)->lessThanOrEqualTo(Carbon::parse($get('date_heure')))) {
                            $fail('La livraison prévue doit être après le départ prévu.');
                        }
                    },
                ]),
            Forms\Components\Select::make('statut')
                ->label('Statut')
                ->options([
                    'planifiee' => 'Planifiée',
                    'en_cours' => 'En cours',
                    'livree' => 'Livrée',
                    'annulee' => 'Annulée',
                ])
                ->default('planifiee')
                ->required(),
            Forms\Components\Repeater::make('courseClients')
                ->label('Clients et marchandises')
                ->relationship()
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->options(fn () => Client::where('company_id', auth()->user()->company_id)->pluck('nom', 'id'))
                        ->required()
                        ->searchable()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                    Forms\Components\TextInput::make('montant_facture')
                        ->label('Montant facturé')
                        ->numeric()
                        ->integer()
                        ->required()
                        ->minValue(0)
                        ->suffix('GNF'),
                    Forms\Components\TextInput::make('type_marchandise')
                        ->label('Type de marchandise')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('poids_kg')
                        ->label('Poids')
                        ->numeric()
                        ->suffix('kg'),
                    Forms\Components\Textarea::make('description_marchandise')
                        ->label('Détail de la marchandise')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->minItems(1)
                ->addActionLabel('Ajouter un client')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date_heure')
                    ->label('Départ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clients.nom')
                    ->label('Clients')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('adresse_depart')
                    ->label('Lieu de départ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('adresse_arrivee')
                    ->label('Arrivée')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.immatriculation')
                    ->label('Véhicule')
                    ->placeholder('Non affecté'),
                Tables\Columns\TextColumn::make('montant_facture_total')
                    ->label('Facturé')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('total_depenses')
                    ->label('Dépenses')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('montant_net')
                    ->label('Net')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('statut_affiche')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (Course $record): string => $record->statut_couleur),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_heure', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'planifiee' => 'Planifiée',
                        'en_cours' => 'En cours',
                        'livree' => 'Livrée',
                        'annulee' => 'Annulée',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->visible(fn (Course $record): bool => $record->statut === 'livree'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(fn (Course $record): bool => $record->statut !== 'livree')
            ->recordUrl(fn (Course $record): string => $record->statut === 'livree'
                ? static::getUrl('view', ['record' => $record])
                : static::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Aucune course');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ExpensesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
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
        return auth()->user()->role === 'responsable' && $record->statut !== 'livree';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->role === 'responsable' && $record->statut !== 'livree';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id)
            ->withSum('expenses', 'montant')
            ->withSum('courseClients', 'montant_facture');
    }
}