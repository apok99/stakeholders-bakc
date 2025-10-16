<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormDefinitionResource\Pages;
use App\Filament\Resources\FormDefinitionResource\RelationManagers;
use App\Models\FormDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class FormDefinitionResource extends Resource
{
    protected static ?string $model = FormDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre del formulario')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Repeater::make('fields')
                    ->label('Campos del formulario')
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de campo')
                            ->options([
                                'text' => 'Texto',
                                'select' => 'Selección',
                                'checkbox' => 'Checkbox',
                                'radio' => 'Radio',
                            ])
                            ->required(),
                        TextInput::make('label')
                            ->label('Etiqueta del campo')
                            ->required(),
                        TagsInput::make('options')
                            ->label('Opciones')
                            ->helperText('Solo para campos de selección, checkbox o radio.')
                            ->requiredif('type', 'select')
                            ->requiredif('type', 'checkbox')
                            ->requiredif('type', 'radio'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre'),
                TextColumn::make('description')->label('Descripción'),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormDefinitions::route('/'),
            'create' => Pages\CreateFormDefinition::route('/create'),
            'edit' => Pages\EditFormDefinition::route('/{record}/edit'),
        ];
    }
}
