<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BienResource\Pages;
use App\Models\Bien;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class BienResource extends Resource
{
    protected static ?string $model = Bien::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'Bienes Informáticos';

    protected static ?string $modelLabel = 'Bien';

    protected static ?string $pluralModelLabel = 'Bienes Informáticos';

    protected static ?string $navigationGroup = 'Inventarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero_inventario')
                    ->label('Número de Inventario')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('numero_inventario_anterior')
                    ->label('Número de Inventario Anterior')
                    ->maxLength(50),

                Forms\Components\TextInput::make('equipo')
                    ->label('Equipo')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('marca')
                    ->label('Marca')
                    ->maxLength(255),

                Forms\Components\TextInput::make('modelo')
                    ->label('Modelo')
                    ->maxLength(255),

                Forms\Components\TextInput::make('serie')
                    ->label('Serie')
                    ->maxLength(255),

                Forms\Components\TextInput::make('ubicacion')
                    ->label('Ubicación')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_inventario')
                    ->label('No. Inventario')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('equipo')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('modelo')
                    ->label('Modelo')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('serie')
                    ->label('Serie')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ubicacion')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable()
                    ->limit(35),
            ])
            ->defaultSort('equipo')
            ->filters([
                Tables\Filters\SelectFilter::make('equipo_tipo')
                    ->label('Tipo de Equipo')
                    ->options(fn () => Bien::query()
                        ->select('equipo')
                        ->distinct()
                        ->orderBy('equipo')
                        ->pluck('equipo', 'equipo')
                        ->toArray()
                    )
                    ->query(fn ($query, array $data) => 
                        $query->when($data['value'], fn ($q, $v) => $q->where('equipo', $v))
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBienes::route('/'),
            'create' => Pages\CreateBien::route('/create'),
            'edit' => Pages\EditBien::route('/{record}/edit'),
        ];
    }
}
