<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Projects';

    public static function form(Form $form): Form
    {
        $fieldAccentClasses = 'focus:border-[#F54963] focus:ring-[#F54963]/80';

        return $form
            ->schema([
                Forms\Components\Section::make('Panorama del proyecto')
                    ->description('Define el contexto y los objetivos clave para darle identidad al proyecto desde el primer vistazo.')
                    ->schema([
                        Forms\Components\TextInput::make('project_context')
                            ->label('Contexto del proyecto')
                            ->placeholder('Transición energética en comunidades rurales')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Resume la oportunidad o el reto que impulsa el proyecto.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpan(6),
                        Forms\Components\TextInput::make('promoting_company')
                            ->label('Empresa impulsora')
                            ->placeholder('Stakeholders Latam')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Quién lidera o financia la iniciativa.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpan(6),
                        Forms\Components\TextInput::make('location')
                            ->label('Ubicación')
                            ->placeholder('Antioquia, Colombia')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('current_phase')
                            ->label('Fase actual')
                            ->placeholder('Diseño colaborativo')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Define el momento actual del proyecto para alinear expectativas.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('main_objective')
                            ->label('Objetivo principal')
                            ->placeholder('Implementar soluciones solares de bajo costo en 10 municipios')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpan(4),
                        Forms\Components\Textarea::make('brief_project_description')
                            ->label('Descripción breve')
                            ->placeholder('Describe el alcance, entregables y resultados esperados en máximo 4 párrafos.')
                            ->required()
                            ->autosize()
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpanFull(),
                    ])
                    ->columns(12)
                    ->extraAttributes([
                        'class' => 'space-y-6 rounded-3xl border border-[#F54963]/30 bg-white/80 p-6 shadow-xl ring-1 ring-[#F54963]/20 backdrop-blur-sm dark:bg-gray-900/80',
                    ]),
                Forms\Components\Section::make('Narrativa y próximos pasos')
                    ->description('Profundiza en sensibilidades, actores clave y los hitos que marcan el camino por recorrer.')
                    ->schema([
                        Forms\Components\Textarea::make('perceived_sensitive_issues')
                            ->label('Temas sensibles percibidos')
                            ->placeholder('Riesgos sociopolíticos, barreras regulatorias, impactos ambientales, etc.')
                            ->required()
                            ->autosize()
                            ->helperText('Detalla riesgos o resistencias que puedan afectar la relación con actores clave.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('known_initial_actors')
                            ->label('Actores iniciales identificados')
                            ->placeholder('Gobernaciones, asociaciones locales, líderes comunitarios…')
                            ->required()
                            ->autosize()
                            ->helperText('Separa los actores con comas para facilitar la lectura.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('next_milestones')
                            ->label('Próximos hitos')
                            ->placeholder("1. Taller de cocreación – agosto\n2. Validación regulatoria – septiembre")
                            ->required()
                            ->autosize()
                            ->helperText('Enlista hitos con fechas estimadas para visualizar la hoja de ruta inmediata.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('reference_links')
                            ->label('Referencias y enlaces')
                            ->placeholder("https://mi-proyecto.com/documento\nhttps://mi-proyecto.com/brief")
                            ->autosize()
                            ->helperText('Agrega uno o más enlaces (uno por línea) con material de soporte.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->columnSpanFull(),
                    ])
                    ->columns(12)
                    ->extraAttributes([
                        'class' => 'space-y-6 rounded-3xl border border-[#F54963]/30 bg-[#F54963]/5 p-6 shadow-lg ring-1 ring-[#F54963]/25 backdrop-blur-sm',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_context')
                    ->label('Project Context')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('promoting_company')
                    ->label('Promoting Company')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('current_phase')
                    ->label('Current Phase')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('main_objective')
                    ->label('Main Objective')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
