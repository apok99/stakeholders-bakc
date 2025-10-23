<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Jobs\ProcessProjectCsvUpload;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('fillFakeData')
                ->label('Generar automáticamente')
                ->icon('heroicon-o-sparkles')
                ->color('danger')
                ->action(fn () => $this->fillFakeData()),
            ...parent::getFormActions(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        return ProjectResource::prepareFormDataForFill($data);
    }

    public function fillFakeData(): void
    {
        $faker = fake();

        $this->form->fill([
            'project_context' => $faker->sentence(3),
            'promoting_company' => $faker->company(),
            'brief_project_description' => $faker->paragraph(4),
            'location' => $faker->city(),
            'current_phase' => $faker->randomElement(['Exploración', 'Diseño', 'Ejecución', 'Cierre']),
            'main_objective' => $faker->sentence(8),
            'perceived_sensitive_issues' => $faker->paragraph(3),
            'known_initial_actors' => implode(', ', $faker->words(4)),
            'next_milestones' => $faker->sentences(2, true),
            'reference_links' => collect(range(1, 2))
                ->map(fn () => $faker->url())
                ->implode(PHP_EOL),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        return ProjectResource::sanitizeFormDefinitionData($data);
    }

    protected function afterCreate(): void
    {
        $project = $this->record;

        $prompt = $this->buildGptPrompt($project);

        try {
            $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $csvContent = $response->choices[0]->message->content;

            // Clean the CSV content
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
                'original_name' => 'stakeholders-gpt.csv',
                'status' => 'pending',
            ]);

            ProcessProjectCsvUpload::dispatch($csvUpload);

        } catch (\Exception $e) {
            Log::error('Error communicating with OpenAI: ' . $e->getMessage());
            // Optionally, notify the user that the stakeholder generation failed.
        }
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
