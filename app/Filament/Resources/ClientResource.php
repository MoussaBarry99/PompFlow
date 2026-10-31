<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'client';

    protected static ?string $pluralModelLabel = 'clients';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nom')
                ->label('Nom du client')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('contact')
                ->label('Contact (téléphone ou e-mail)')
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact')
                    ->label('Contact')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nom')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun client');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
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