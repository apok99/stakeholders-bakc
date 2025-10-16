<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\FormDefinition;
use App\Jobs\ProcessProjectCsvUpload;
use App\Models\Project;
use App\Models\ProjectCsvUpload;
use Filament\Forms;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Projects';

    public static function form(Form $form): Form
    {
        $fieldAccentClasses = static::fieldAccentClasses();

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
                                        ->acceptedFileTypes([
                                            'text/csv',
                                            'text/plain',
                                            'application/csv',
                                            'application/vnd.ms-excel',
                                            'application/octet-stream',
                                        ])
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
        $fieldAccentClasses = static::fieldAccentClasses();

        return [
            Forms\Components\Select::make('form_definition_id')
                ->label('Categoría')
                ->options(fn () => FormDefinition::query()->pluck('name', 'id'))
                ->searchable()
                ->placeholder('Selecciona una categoría')
                ->reactive()
                ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                    $set('form_responses', static::initializeFormResponseState($state, $get('form_responses')));
                })
                ->afterStateHydrated(function ($state, ?string $operation, Set $set, Get $get): void {
                    if (! filled($state)) {
                        return;
                    }

                    $set('form_responses', static::initializeFormResponseState($state, $get('form_responses')));
                })
                ->helperText('Las categorías se gestionan desde la pestaña “Form Definitions”.')
                ->columnSpan(6),
            Forms\Components\Hidden::make('form_responses')
                ->default([])
                ->dehydrated()
                ->afterStateHydrated(fn (Forms\Components\Hidden $component, $state) => $component->state(static::extractResponseAnswers($state))),
            Forms\Components\Fieldset::make('form_definition_questions')
                ->label('Preguntas de la categoría')
                ->visible(fn (Get $get) => filled($get('form_definition_id')))
                ->schema(fn (Get $get) => static::buildQuestionComponents($get('form_definition_id'), $fieldAccentClasses))
                ->columns(12)
                ->extraAttributes([
                    'class' => 'space-y-4 rounded-2xl border border-blue-500/20 bg-white/70 p-4 shadow-inner backdrop-blur-sm dark:border-blue-500/10 dark:bg-gray-900/60',
                ]),
            FormActions::make([
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
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/csv',
                                'application/vnd.ms-excel',
                                'application/octet-stream',
                            ])
                            ->required()
                            ->helperText('Selecciona un archivo .csv con los datos de stakeholders o respuestas preparadas.')
                            ->dehydrated(false)
                            ->preserveFilenames(),
                    ])
                    ->action(function (array $data, Get $get, $livewire): void {
                        $uploaded = $data['csv_file'] ?? null;

                        if (! $uploaded) {
                            Notification::make()
                                ->title('No se detectó el archivo CSV')
                                ->body('Intenta nuevamente y asegúrate de adjuntar un archivo .csv válido.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $formDefinitionId = $get('form_definition_id');
                        $projectId = method_exists($livewire, 'getRecord') && $livewire->getRecord()
                            ? $livewire->getRecord()->getKey()
                            : null;

                        $csvUpload = static::storeCsvUpload($uploaded, $formDefinitionId, $projectId);

                        if (! $csvUpload) {
                            Notification::make()
                                ->title('No fue posible guardar el archivo')
                                ->body('Revisa los permisos de almacenamiento e inténtalo nuevamente.')
                                ->danger()
                                ->send();

                            return;
                        }

                        ProcessProjectCsvUpload::dispatch($csvUpload);

                        Notification::make()
                            ->title('CSV cargado correctamente')
                            ->body('El archivo “'.$csvUpload->original_name.'” quedó registrado y será procesado en breve.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Get $get) => filled($get('form_definition_id'))),
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
                    ->visible(fn (Get $get, $livewire) => $livewire instanceof Pages\CreateProject && filled($get('form_definition_id')))
                    ->requiresConfirmation(false),
            ])
                ->visible(fn (Get $get) => filled($get('form_definition_id')))
                ->columnSpan(6)
                ->extraAttributes([
                    'class' => 'flex flex-col items-center justify-center gap-3 py-6',
                ]),
        ];
    }

    protected static function storeCsvUpload($uploaded, ?int $formDefinitionId, ?int $projectId): ?ProjectCsvUpload
    {
        $storedFile = static::persistUploadedFile($uploaded);

        if (! $storedFile) {
            return null;
        }

        [$path, $originalName, $disk] = $storedFile;

        return ProjectCsvUpload::create([
            'project_id' => $projectId,
            'form_definition_id' => $formDefinitionId,
            'storage_disk' => $disk,
            'file_path' => $path,
            'original_name' => $originalName,
            'status' => ProjectCsvUpload::STATUS_PENDING,
        ]);
    }

    protected static function persistUploadedFile($uploaded): ?array
    {
        $disk = config('filesystems.default', 'local');

        if ($uploaded instanceof TemporaryUploadedFile) {
            $extension = $uploaded->getClientOriginalExtension();
            $filename = Str::uuid().($extension ? '.'.$extension : '');
            $path = $uploaded->storeAs('project-imports/csv', $filename, $disk);

            if (! $path) {
                return null;
            }

            return [$path, $uploaded->getClientOriginalName(), $disk];
        }

        if (is_string($uploaded)) {
            if (! Storage::disk($disk)->exists($uploaded)) {
                return null;
            }

            return [$uploaded, basename($uploaded), $disk];
        }

        return null;
    }

    public static function sanitizeFormDefinitionData(array $data): array
    {
        if (! static::supportsFormDefinitions()) {
            unset($data['form_definition_id'], $data['form_responses']);

            return $data;
        }

        if (blank($data['form_definition_id'] ?? null)) {
            $data['form_definition_id'] = null;
            $data['form_responses'] = [];

            return $data;
        }

        $formDefinition = FormDefinition::find($data['form_definition_id']);

        if (! $formDefinition) {
            $data['form_responses'] = [];

            return $data;
        }

        $answers = static::extractResponseAnswers($data['form_responses'] ?? []);

        $data['form_responses'] = collect($formDefinition->fields ?? [])
            ->values()
            ->map(function ($field, $index) use ($answers) {
                return [
                    'question' => $field['label'] ?? 'Pregunta '.($index + 1),
                    'answer' => $answers[$index] ?? null,
                ];
            })
            ->toArray();

        return $data;
    }

    public static function prepareFormDataForFill(array $data): array
    {
        if (! static::supportsFormDefinitions()) {
            return $data;
        }

        if (isset($data['form_responses'])) {
            $data['form_responses'] = static::extractResponseAnswers($data['form_responses']);
        }

        return $data;
    }

    protected static function extractResponseAnswers($responses): array
    {
        return collect($responses ?? [])
            ->map(function ($value) {
                if (is_array($value)) {
                    return $value['answer'] ?? null;
                }

                return $value;
            })
            ->values()
            ->toArray();
    }

    protected static function initializeFormResponseState($formDefinitionId, $currentResponses): array
    {
        if (! $formDefinitionId) {
            return [];
        }

        $formDefinition = FormDefinition::find($formDefinitionId);

        if (! $formDefinition) {
            return [];
        }

        $answers = static::extractResponseAnswers($currentResponses);

        return collect($formDefinition->fields ?? [])
            ->values()
            ->map(fn ($field, $index) => $answers[$index] ?? null)
            ->toArray();
    }

    protected static function buildQuestionComponents($formDefinitionId, string $fieldAccentClasses): array
    {
        if (! $formDefinitionId) {
            return [];
        }

        $formDefinition = FormDefinition::find($formDefinitionId);

        if (! $formDefinition) {
            return [];
        }

        $fields = collect($formDefinition->fields ?? [])->values();

        if ($fields->isEmpty()) {
            return [
                Forms\Components\Placeholder::make('no_form_questions')
                    ->content('Esta categoría aún no tiene preguntas configuradas.')
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'text-sm text-gray-600 dark:text-gray-300',
                    ]),
            ];
        }

        return $fields
            ->map(function ($field, $index) use ($fieldAccentClasses) {
                $statePath = "form_responses.{$index}";
                $label = $field['label'] ?? 'Pregunta '.($index + 1);
                $helper = $field['description'] ?? null;
                $placeholder = $field['placeholder'] ?? null;
                $required = (bool) ($field['required'] ?? false);
                $options = collect($field['options'] ?? [])
                    ->mapWithKeys(function ($option, $key) {
                        if (is_array($option)) {
                            $value = Arr::get($option, 'value', is_int($key) ? ($option['label'] ?? $key) : $key);
                            $label = Arr::get($option, 'label', $value);

                            return [$value => $label];
                        }

                        return [is_int($key) ? $option : $key => $option];
                    })
                    ->all();

                return match ($field['type'] ?? 'text') {
                    'select' => Forms\Components\Select::make($statePath)
                        ->label($label)
                        ->options($options)
                        ->placeholder($placeholder ?? 'Selecciona una opción')
                        ->helperText($helper)
                        ->required($required)
                        ->searchable()
                        ->columnSpanFull(),
                    'checkbox' => Forms\Components\Checkbox::make($statePath)
                        ->label($label)
                        ->helperText($helper)
                        ->required($required)
                        ->columnSpanFull(),
                    'radio' => Forms\Components\Radio::make($statePath)
                        ->label($label)
                        ->options($options)
                        ->helperText($helper)
                        ->required($required)
                        ->columnSpanFull(),
                    'textarea' => Forms\Components\Textarea::make($statePath)
                        ->label($label)
                        ->placeholder($placeholder)
                        ->helperText($helper)
                        ->required($required)
                        ->autosize()
                        ->extraAttributes(['class' => $fieldAccentClasses])
                        ->columnSpanFull(),
                    default => Forms\Components\TextInput::make($statePath)
                        ->label($label)
                        ->placeholder($placeholder)
                        ->helperText($helper)
                        ->required($required)
                        ->extraInputAttributes(['class' => $fieldAccentClasses])
                        ->columnSpanFull(),
                };
            })
            ->all();
    }

    protected static function fieldAccentClasses(): string
    {
        return 'focus:border-blue-500 focus:ring-blue-500/80';
    }
}
