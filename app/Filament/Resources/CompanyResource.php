<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Opérations';

    protected static ?string $navigationLabel = 'Sociétés';

    protected static ?string $modelLabel = 'société';

    protected static ?string $pluralModelLabel = 'sociétés';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nom')
                ->label('Nom de la société')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('adresse')
                ->label('Adresse')
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Société')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('adresse')
                    ->label('Adresse')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Membres')
                    ->counts('users'),
                Tables\Columns\TextColumn::make('vehicles_count')
                    ->label('Véhicules')
                    ->counts('vehicles'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune société');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'super_admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'super_admin';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->role === 'super_admin';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->role === 'super_admin';
    }
}