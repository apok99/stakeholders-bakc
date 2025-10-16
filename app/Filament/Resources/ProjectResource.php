<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\FormDefinition;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Projects';

    public static function form(Form $form): Form
    {
        $fieldAccentClasses = 'focus:border-blue-500 focus:ring-blue-500/80';

        $supportsFormDefinitions = static::supportsFormDefinitions();

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
                            ->prefixIcon('heroicon-o-light-bulb')
                            ->columnSpan(6),
                        Forms\Components\TextInput::make('promoting_company')
                            ->label('Empresa impulsora')
                            ->placeholder('Stakeholders Latam')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Quién lidera o financia la iniciativa.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->columnSpan(6),
                        Forms\Components\TextInput::make('location')
                            ->label('Ubicación')
                            ->placeholder('Antioquia, Colombia')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->prefixIcon('heroicon-o-map-pin')
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('current_phase')
                            ->label('Fase actual')
                            ->placeholder('Diseño colaborativo')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Define el momento actual del proyecto para alinear expectativas.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->prefixIcon('heroicon-o-tag')
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('main_objective')
                            ->label('Objetivo principal')
                            ->placeholder('Implementar soluciones solares de bajo costo en 10 municipios')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->prefixIcon('heroicon-o-rocket-launch')
                            ->columnSpan(4),
                        Forms\Components\Group::make()
                            ->schema($supportsFormDefinitions
                                ? static::getFormDefinitionSchema()
                                : [
                                    Forms\Components\Placeholder::make('form_definition_unavailable')
                                        ->label('Categoría')
                                        ->content('Ejecuta las migraciones pendientes para habilitar la selección de categoría y el cuestionario asociado.')
                                        ->columnSpanFull()
                                        ->extraAttributes([
                                            'class' => 'flex items-center justify-center rounded-2xl border border-dashed border-blue-500/40 bg-white/60 p-6 text-center text-sm text-blue-600 dark:border-blue-400/40 dark:bg-gray-900/60 dark:text-blue-300',
                                        ]),
                                ])
                            ->columns(12)
                            ->columnSpanFull()
                            ->extraAttributes([
                                'class' => 'space-y-4 rounded-3xl border border-blue-500/30 bg-blue-500/10 p-4 shadow-inner backdrop-blur-sm dark:border-blue-500/20 dark:bg-gray-900/40',
                            ]),
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
                        'class' => 'space-y-6 rounded-3xl border border-blue-500/30 bg-white/80 p-6 shadow-xl ring-1 ring-blue-500/20 backdrop-blur-sm dark:bg-gray-900/80',
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
                        Forms\Components\TagsInput::make('known_initial_actors')
                            ->label('Actores iniciales identificados')
                            ->placeholder('Gobernaciones, asociaciones locales, líderes comunitarios…')
                            ->required()
                            ->helperText('Separa los actores con comas para facilitar la lectura.')
                            ->extraInputAttributes(['class' => $fieldAccentClasses])
                            ->afterStateHydrated(function (Forms\Components\TagsInput $component, $state): void {
                                if (is_string($state)) {
                                    $component->state(
                                        collect(preg_split('/\s*,\s*/', $state, -1, PREG_SPLIT_NO_EMPTY))
                                            ->filter()
                                            ->values()
                                            ->all()
                                    );
                                }
                            })
                            ->dehydrateStateUsing(fn ($state) => collect($state)->filter()->implode(', '))
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
                        'class' => 'space-y-6 rounded-3xl border border-blue-500/30 bg-blue-500/5 p-6 shadow-lg ring-1 ring-blue-500/25 backdrop-blur-sm',
                    ]),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        $supportsFormDefinitions = static::supportsFormDefinitions();

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
                Tables\Columns\TextColumn::make('formDefinition.name')
                    ->label('Categoría')
                    ->toggleable()
                    ->visible($supportsFormDefinitions),
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

    protected static function supportsFormDefinitions(): bool
    {
        return Schema::hasColumn('projects', 'form_definition_id')
            && Schema::hasColumn('projects', 'form_responses');
    }

    protected static function getFormDefinitionSchema(): array
    {
        return [
            Forms\Components\Select::make('form_definition_id')
                ->label('Categoría')
                ->options(fn () => FormDefinition::query()->pluck('name', 'id'))
                ->searchable()
                ->placeholder('Selecciona una categoría')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, Forms\Components\Component $component): void {
                    $set('form_responses', []);

                    if (filled($state)) {
                        $component->getLivewire()->mountFormComponentAction($component->getStatePath(), 'fillFormDefinition');
                    }
                })
                ->helperText('Las categorías se gestionan desde la pestaña “Form Definitions”.')
                ->columnSpan(6),
            Forms\Components\Hidden::make('form_responses')
                ->default([])
                ->dehydrated()
                ->afterStateHydrated(fn (Forms\Components\Hidden $component, $state) => $component->state($state ?? [])),
            FormActions::make([
                FormAction::make('fillFormDefinition')
                    ->label('Responder preguntas ahora')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading(fn (callable $get) => optional(FormDefinition::find($get('form_definition_id')))->name ?? 'Responder formulario')
                    ->modalSubmitActionLabel('Guardar respuestas')
                    ->modalWidth('screen-xl')
                    ->modalAlignment('top')
                    ->visible(fn (callable $get) => filled($get('form_definition_id')))
                    ->form(function (callable $get): array {
                        $formDefinition = FormDefinition::find($get('form_definition_id'));

                        if (! $formDefinition) {
                            return [];
                        }

                        return collect($formDefinition->fields ?? [])
                            ->map(function ($field, $index) {
                                $component = match ($field['type'] ?? 'text') {
                                    'select' => Forms\Components\Select::make("responses.{$index}")
                                        ->options(collect($field['options'] ?? [])->mapWithKeys(fn ($option) => [$option => $option])->all()),
                                    'checkbox' => Forms\Components\Checkbox::make("responses.{$index}"),
                                    'radio' => Forms\Components\Radio::make("responses.{$index}")
                                        ->options(collect($field['options'] ?? [])->mapWithKeys(fn ($option) => [$option => $option])->all()),
                                    'textarea' => Forms\Components\Textarea::make("responses.{$index}"),
                                    default => Forms\Components\TextInput::make("responses.{$index}"),
                                };

                                return $component
                                    ->label($field['label'] ?? 'Pregunta '.($index + 1))
                                    ->columnSpanFull();
                            })
                            ->values()
                            ->all();
                    })
                    ->fillForm(function (callable $get): array {
                        $responses = collect($get('form_responses') ?? []);

                        if ($responses->isEmpty()) {
                            return [];
                        }

                        return [
                            'responses' => $responses->pluck('answer')->toArray(),
                        ];
                    })
                    ->action(function (array $data, callable $set, callable $get): void {
                        $formDefinition = FormDefinition::find($get('form_definition_id'));

                        if (! $formDefinition) {
                            return;
                        }

                        $responses = collect($formDefinition->fields ?? [])
                            ->map(function ($field, $index) use ($data) {
                                $answers = $data['responses'] ?? [];

                                return [
                                    'question' => $field['label'] ?? 'Pregunta '.($index + 1),
                                    'answer' => $answers[$index] ?? null,
                                ];
                            })
                            ->toArray();

                        $set('form_responses', $responses);
                    }),
                FormAction::make('uploadCsv')
                    ->label('Subir CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('secondary')
                    ->modalIcon('heroicon-o-arrow-up-tray')
                    ->modalHeading('Importar respuestas desde CSV')
                    ->modalDescription('Carga un archivo CSV con las respuestas para esta categoría.')
                    ->modalSubmitActionLabel('Subir archivo')
                    ->form([
                        Forms\Components\FileUpload::make('csv_file')
                            ->label('Archivo CSV')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText('Selecciona un archivo .csv con los datos de stakeholders o respuestas preparadas.')
                            ->dehydrated(false)
                            ->preserveFilenames(),
                    ])
                    ->action(function (array $data): void {
                        $uploaded = $data['csv_file'] ?? null;

                        if (! $uploaded) {
                            Notification::make()
                                ->title('No se detectó el archivo CSV')
                                ->body('Intenta nuevamente y asegúrate de adjuntar un archivo .csv válido.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $fileName = is_string($uploaded)
                            ? basename($uploaded)
                            : $uploaded->getClientOriginalName();

                        Notification::make()
                            ->title('CSV cargado correctamente')
                            ->body('El archivo “'.$fileName.'” quedó listo para procesarse.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (callable $get) => filled($get('form_definition_id'))),
                FormAction::make('generateSampleData')
                    ->label('Generar automáticamente')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->action(function ($livewire): void {
                        if (method_exists($livewire, 'fillFakeData')) {
                            $livewire->fillFakeData();

                            Notification::make()
                                ->title('Contenido generado')
                                ->body('Hemos completado los campos principales con un ejemplo listo para editar.')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(fn (callable $get, $livewire) => $livewire instanceof Pages\CreateProject && filled($get('form_definition_id')))
                    ->requiresConfirmation(false),
            ])
                ->visible(fn (callable $get) => filled($get('form_definition_id')))
                ->columnSpan(6)
                ->extraAttributes([
                    'class' => 'flex flex-col items-center justify-center gap-3 py-6',
                ]),
            Forms\Components\ViewField::make('form_responses_preview')
                ->label('Respuestas guardadas')
                ->view('filament.forms.components.form-responses-preview')
                ->visible(fn (callable $get) => filled($get('form_responses')))
                ->viewData(function (callable $get): array {
                    $formDefinition = FormDefinition::find($get('form_definition_id'));

                    return [
                        'formName' => $formDefinition?->name,
                        'responses' => $get('form_responses') ?? [],
                    ];
                })
                ->columnSpanFull()
                ->extraAttributes([
                    'class' => 'mt-4 rounded-2xl border border-blue-500/20 bg-white/70 p-4 shadow-sm backdrop-blur dark:border-blue-500/10 dark:bg-gray-900/60',
                ]),
            Forms\Components\Section::make('Brandwatch')
                ->description('Sincroniza insights de escucha social importando reportes directamente desde Brandwatch.')
                ->schema([
                    Forms\Components\Placeholder::make('brandwatch_help')
                        ->label('¿Por qué conectar Brandwatch?')
                        ->content('Centraliza la conversación digital del proyecto y cruza menciones con los stakeholders identificados.')
                        ->columnSpanFull()
                        ->extraAttributes([
                            'class' => 'text-sm text-gray-600 dark:text-gray-300',
                        ]),
                    FormActions::make([
                        FormAction::make('uploadBrandwatch')
                            ->label('Subir Brandwatch')
                            ->icon('heroicon-o-cloud-arrow-up')
                            ->color('info')
                            ->modalIcon('heroicon-o-cloud-arrow-up')
                            ->modalHeading('Conectar reporte de Brandwatch')
                            ->modalDescription('Adjunta el CSV exportado desde Brandwatch para sincronizar menciones y sentimientos.')
                            ->modalSubmitActionLabel('Subir reporte')
                            ->form([
                                Forms\Components\FileUpload::make('brandwatch_file')
                                    ->label('Reporte Brandwatch')
                                    ->acceptedFileTypes(['text/csv', 'text/plain'])
                                    ->required()
                                    ->helperText('Carga el archivo exportado desde Brandwatch en formato CSV.')
                                    ->dehydrated(false)
                                    ->preserveFilenames(),
                            ])
                            ->action(function (array $data): void {
                                $uploaded = $data['brandwatch_file'] ?? null;

                                if (! $uploaded) {
                                    Notification::make()
                                        ->title('No se detectó el reporte de Brandwatch')
                                        ->body('Intenta nuevamente y verifica que el archivo exportado sea .csv.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $fileName = is_string($uploaded)
                                    ? basename($uploaded)
                                    : $uploaded->getClientOriginalName();

                                Notification::make()
                                    ->title('Reporte Brandwatch recibido')
                                    ->body('El archivo “'.$fileName.'” ya está disponible para revisión.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                        ->columnSpanFull()
                        ->extraAttributes([
                            'class' => 'flex flex-col items-center justify-center gap-3 py-6',
                        ]),
                ])
                ->columns(12)
                ->extraAttributes([
                    'class' => 'space-y-4 rounded-3xl border border-blue-500/30 bg-white/80 p-6 shadow-xl ring-1 ring-blue-500/20 backdrop-blur-sm dark:border-blue-500/20 dark:bg-gray-900/70',
                ]),
        ];
    }

    public static function sanitizeFormDefinitionData(array $data): array
    {
        if (! static::supportsFormDefinitions()) {
            unset($data['form_definition_id'], $data['form_responses']);
        }

        return $data;
    }
}
