<?php

namespace Database\Seeders;

use App\Models\FormDefinition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FormDefinition::truncate();

        FormDefinition::create([
            'name' => '1. Infraestructura, construcción y urbanismo',
            'fields' => [
                [
                    'type' => 'textarea',
                    'label' => '¿Cómo identificas a los actores más relevantes en proyectos con alto impacto territorial, como comunidades locales o autoridades?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Qué criterios usas para priorizar entre stakeholders con intereses opuestos o diferentes niveles de poder?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Qué estrategias aplicas para prevenir o gestionar conflictos durante la ejecución de la obra?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Cómo integras la comunicación con los stakeholders en el cronograma general del proyecto?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Podrías compartir un ejemplo en el que un mapeo de stakeholders haya ayudado a evitar retrasos o mejorar la aceptación social del proyecto?',
                    'options' => [],
                ],
            ],
        ]);

        FormDefinition::create([
            'name' => '2. Energía y medio ambiente',
            'fields' => [
                [
                    'type' => 'textarea',
                    'label' => '¿Cómo equilibras las expectativas de los distintos actores, desde comunidades locales hasta reguladores y grupos ambientalistas?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Qué metodología utilizas para evaluar el grado de apoyo o resistencia hacia un proyecto energético o ambiental?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿De qué manera adaptas el mapa de stakeholders cuando surgen nuevas normativas o actores relevantes?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Cómo se integra el mapa de stakeholders dentro de la estrategia de sostenibilidad o comunicación ambiental?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Puedes citar un caso donde la gestión de stakeholders haya contribuido a obtener la “licencia social para operar”?',
                    'options' => [],
                ],
            ],
        ]);

        FormDefinition::create([
            'name' => '3. Sector público y administración gubernamental',
            'fields' => [
                [
                    'type' => 'textarea',
                    'label' => '¿Qué enfoque utilizas para mapear stakeholders en políticas públicas con múltiples niveles de gobierno involucrados?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Cómo aseguras que los grupos menos visibles o vulnerables estén representados en el análisis?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Qué mecanismos empleas para mantener el diálogo y la transparencia con los distintos actores sociales e institucionales?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Cómo mides el impacto de la participación o involucramiento de los stakeholders en los resultados de una política pública?',
                    'options' => [],
                ],
                [
                    'type' => 'textarea',
                    'label' => '¿Qué errores comunes observas en proyectos públicos al elaborar o usar un mapa de stakeholders y cómo los evitas?',
                    'options' => [],
                ],
            ],
        ]);
    }
}
