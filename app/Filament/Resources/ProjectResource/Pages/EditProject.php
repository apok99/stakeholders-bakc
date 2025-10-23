<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Models\ProjectCsvUpload;
use App\Filament\Resources\ProjectResource;
use App\Jobs\ProcessProjectCsvUpload;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    public function downloadCsv($uploadId)
    {
        $upload = ProjectCsvUpload::find($uploadId);

        if (!$upload) {
            return;
        }

        if ($upload->project_id !== $this->getRecord()->id) {
            return;
        }

        return Storage::disk($upload->storage_disk)->download($upload->file_path, $upload->original_name);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('fillExampleData')
                ->label('Autorellenar Ejemplo')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->action(function () {
                    $this->form->fill([
                        'project_context' => 'Desarrollo de complejo turístico y de ocio',
                        'promoting_company' => 'Gran Casino Costa del Sol S.A.',
                        'brief_project_description' => 'Proyecto para la construcción y operación de un nuevo casino y hotel de lujo en primera línea de playa. El complejo busca atraer turismo de alto poder adquisitivo, generar empleo local y posicionar la región como un destino de ocio de referencia internacional. Incluirá salas de juego, restaurantes de alta cocina, un hotel de 5 estrellas y un centro de convenciones.',
                        'location' => 'España · Andalucía · Marbella',
                        'current_phase' => 'Tramitación',
                        'main_objective' => 'Económico y Reputacional',
                        'perceived_sensitive_issues' => 'Impacto ambiental en la costa, posible aumento de la ludopatía, rechazo de asociaciones de vecinos por el modelo de turismo, competencia con otros destinos.',
                        'known_initial_actors' => 'Ayuntamiento de Marbella, Junta de Andalucía, Demarcación de Costas, Ecologistas en Acción, Asociación de Hoteleros de la Costa del Sol',
                        'next_milestones' => 'Obtención de la Declaración de Impacto Ambiental (DIA), Aprobación del plan urbanístico por el Ayuntamiento, Ronda de financiación con inversores.',
                        'reference_links' => '',
                        'form_definition_id' => 1,
                        'form_responses' => [
                            'Otros casinos y resorts de lujo en la Costa del Sol, como Casino Marbella y el Hotel Puente Romano.',
                            'Ley del Juego de Andalucía, normativa urbanística municipal de Marbella, Ley de Costas, regulaciones de impacto ambiental de la Junta de Andalucía.',
                            'Sí, varios hoteles de lujo y complejos turísticos, pero un casino de esta magnitud en primera línea de playa es menos común.',
                            'El partido en el gobierno local (PP) es favorable por la inversión y el empleo. La oposición (PSOE, IU) muestra preocupación por el impacto social y ambiental.',
                            'Diarios locales (Diario Sur, La Opinión de Málaga), prensa económica (Expansión), y blogs de turismo y ocio.',
                            'Algunos influencers de turismo de lujo podrían ser favorables. Activistas ambientales locales se han manifestado en contra.',
                            'Dividido. Una parte ve oportunidades de empleo y desarrollo, otra teme la masificación, el impacto ambiental y los problemas sociales asociados al juego.',
                            'Ecologistas en Acción, Plataforma Salvemos la Costa, asociaciones de vecinos de Marbella.',
                            'Constructora Sacyr, estudio de arquitectura B720 Fermín Vázquez, empresa de gestión de casinos Cirsa.',
                            'Sí, importación de materiales de construcción de alta gama, tecnología de juego de proveedores internacionales, personal especializado de otros países.'
                        ]
                    ]);

                    Notification::make()
                        ->title('Campos autorellenados')
                        ->body('Se han rellenado los campos del formulario con datos de ejemplo sobre un casino.')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('callGpt')
                ->label('Generar Stakeholders (GPT)')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Confirmar generación de stakeholders')
                ->modalDescription('Esto enviará la información del proyecto a la API de OpenAI para generar una nueva lista de stakeholders. El proceso puede tardar unos segundos.')
                ->action(function () {
                    try {
                        $project = $this->getRecord();
                        $prompt = $this->buildGptPrompt($project);

                        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
                            'model' => 'gpt-4o',
                            'messages' => [
                                ['role' => 'user', 'content' => $prompt],
                            ],
                        ]);

                        $csvContent = $response->choices[0]->message->content;

                        $csvContent = trim($csvContent);
                        if (substr($csvContent, 0, 7) === '```csv') {
                            $csvContent = substr($csvContent, 7);
                            $csvContent = trim($csvContent, '`');
                        }

                        $fileName = 'stakeholder-analysis-' . $project->id . '-' . now()->timestamp . '.csv';
                        $filePath = 'project-csvs/' . $fileName;

                        Storage::disk('private')->put($filePath, $csvContent);

                        $csvUpload = $project->csvUploads()->create([
                            'form_definition_id' => $project->form_definition_id,
                            'storage_disk' => 'private',
                            'file_path' => $filePath,
                            'original_name' => 'stakeholders-gpt-' . now()->format('Y-m-d') . '.csv',
                            'status' => 'pending',
                        ]);

                        ProcessProjectCsvUpload::dispatch($csvUpload);

                        Notification::make()
                            ->title('Análisis de stakeholders en proceso')
                            ->body('Hemos recibido la respuesta de la IA y la estamos procesando. La lista de stakeholders se actualizará en breve.')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Log::error('Error communicating with OpenAI on edit page: ' . $e->getMessage());
                        Notification::make()
                            ->title('Error al generar stakeholders')
                            ->body('No se pudo completar la solicitud a la API de OpenAI. Por favor, revisa los logs para más detalles.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        return ProjectResource::prepareFormDataForFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        return ProjectResource::sanitizeFormDefinitionData($data);
    }

    private function buildGptPrompt($project): string
    {
        $projectContext = "Empresa promotora: {$project->promoting_company}\n" .
            "3–5 líneas de descripción (qué es, dónde, para qué): {$project->brief_project_description}\n" .
            "Localización (país · región · municipio): {$project->location}\n" .
            "Fase actual (idea / tramitación / construcción / operación): {$project->current_phase}\n" .
            "Objetivo principal (Económico / Estratégico / Reputacional / Regulatorio; 1–2): {$project->main_objective}\n" .
            "Temas sensibles percibidos (2–4): {$project->perceived_sensitive_issues}\n" .
            "Actores iniciales conocidos (personas/entidades): {$project->known_initial_actors}\n" .
            "Próximos hitos (si los hay): {$project->next_milestones}\n" .
            "Enlaces de referencia (opc.): {$project->reference_links}";

        $refiningAnswers = collect($project->form_responses ?? [])
            ->map(function ($response, $index) {
                $question = $response['question'] ?? 'Pregunta ' . ($index + 1);
                $answer = $response['answer'] ?? 'Respuesta pendiente';
                return ($index + 1) . ". {$question},{$answer}";
            })
            ->implode("\n");

        if (empty($refiningAnswers)) {
            $refiningAnswers = "No se proporcionaron respuestas de afinado.";
        }

        return <<<PROMPT
Rol: Eres un analista senior de corporate affairs especializado en mapeo de stakeholders.

Objetivo: A partir del Contexto de proyecto y las Respuestas de afinado, genera una lista de ≥50 stakeholders (mezcla de entidades, personas e influencers), cubriendo todos los niveles relevantes (local, autonómico, nacional, UE) y la cadena de valor.

Salida: ÚNICAMENTE un CSV con la cabecera indicada y sin texto adicional.


1) ENTRADAS (el usuario te las pegará antes de invocarte)

Contexto del proyecto con estos campos:

{$projectContext}


Respuestas de afinado (10) en formato Pregunta,Respuesta.

{$refiningAnswers}


2) REGLAS DE SELECCIÓN

Volumen mínimo: 50 filas únicas (sin duplicados).

Híbrido realista: mezcla personas con nombre y apellido, entidades e influencers (al menos 8–10 perfiles influencer/creador/periodista especializado si son pertinentes al caso).

Cobertura multinivel: incluye local, autonómico, nacional, UE/Internacional cuando aplique.

Cadena de valor y entorno: promotor, reguladores, gobiernos, financiadores, empresas, asociaciones/sector, sindicatos, ONGs/plataformas, comunidad local, academia/think tanks, justicia/órganos garantes, medios/creadores.

Nombres propios cuando existan y sean pertinentes; evita genéricos salvo rol estable (p. ej. “Dirección General X” si el cargo cambia).

Especificidad en “Rol o función” y “Organización/Ámbito”; evita descripciones vagas.

Sin inventar hechos: si un dato no es seguro o no aplica, deja el campo vacío ("").



3) FORMATO DE SALIDA (CSV ESTABLE PARA BD)
Cabecera obligatoria (en este orden exacto):
id,Stakeholder,Tipo,Categoría,Subcategoría,Rol o función en el caso,Organización / Ámbito,Ámbito_nivel,País,Región,Municipio,Persona_Cargo,Entidad_Madre,Influencer_Plataforma,Influencer_Alias,Contacto_URL,Fuente_URL,Posición_inicial,Prioridad_preliminar,Notas

Definiciones y valores permitidos

id: slug único en kebab-case (ej.: miteco-teresa-ribera, aena-sme, coexphal).

Stakeholder: nombre visual (persona con nombre y apellido; entidad con denominación oficial).

Tipo: Persona | Entidad.

Categoría (enum):

Empresa promotora | Empresa | Asociación empresarial | Sindicato | Gobierno central | Gobierno autonómico | Gobierno local | Regulador/Agencia estatal | Institución UE | Finanzas/Inversor | Órgano garante/Poder judicial | ONG/Plataforma ciudadana | Comunidad local | Academia/Think tank | Medios | Medios digitales/Influencer | Operador/Infraestructura | Consumidores/Usuario

Subcategoría: libre controlado (p. ej., “aerolínea”, “consejería”, “cooperativa”, “patronal”, “agencia ambiental”, “cofradía pescadores”, etc.).

Rol o función en el caso: 1–2 frases (máx. 180 caracteres) sobre por qué es relevante.

Organización / Ámbito: matriz/holding o dependencia (p. ej., “MITECO”, “Junta de Andalucía”, “Acciona Energía”).

Ámbito_nivel (enum): Local | Autonómico | Nacional | UE/Internacional | Privado.

País/Región/Municipio: geografía principal del actor.

Persona_Cargo: si Tipo=Persona, título/puesto (ministro/a, CEO, alcalde/sa…); si no aplica, "".

Entidad_Madre: grupo/ministerio/holding del que depende; si no aplica, "".

Influencer_Plataforma: si es creador/periodista digital, plataforma principal (X, YouTube, TikTok, Substack, Podcast, etc.); si no aplica, "".

Influencer_Alias: alias/handle; si no aplica, "".

Contacto_URL: página oficial/perfil social más útil; si incierto, "".

Fuente_URL: enlace principal de referencia (sitio oficial o medio reputado); si incierto, "".

Posición_inicial (enum orientativa, si se deduce por el contexto general; de lo contrario Desconocida): Favorable | Activista | Neutral | Desconocida.

Prioridad_preliminar (orientativa para trabajo del consultor): Alta | Media | Baja.

Notas: matiz breve (máx. 160 caracteres); si no hay, "".

Reglas de formato:

Devuelve solo el CSV (sin explicaciones, sin Markdown).

Citas dobles alrededor de campos que contengan comas.

Sin filas en blanco, sin totales.

Máx. ~180–200 caracteres en campos de texto descriptivo para evitar desbordes.



4) CONSISTENCIA Y CALIDAD (autochecks que debes cumplir)

≥50 filas y sin duplicados de id.

Mezcla: ≥20 personas y ≥8 influencers/creadores/periodistas especializados cuando tenga sentido por el caso; el resto entidades.

Cobertura multinivel: al menos 1/4 de los registros deben pertenecer a otro nivel distinto del principal del proyecto.

Si una persona pertenece a una entidad, rellena Organización / Ámbito y Entidad_Madre cuando aplique.

Campos no aplicables → "" (vacío), nunca “N/A”.



5) INSTRUCCIÓN FINAL

Genera la salida directamente en CSV con la cabecera indicada y al menos 50 filas cumpliendo todas las reglas anteriores. Toda la información debe estar estrictamente centrada en la localización del proyecto (Marbella, Málaga, Andalucía, España). No añadas texto fuera del CSV.
PROMPT;
    }
}
