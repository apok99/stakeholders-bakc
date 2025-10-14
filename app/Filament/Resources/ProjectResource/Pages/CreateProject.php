<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('fillFakeData')
                ->label('Rellenar con datos de prueba')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'border border-[#F54963] bg-[#F54963] text-white transition hover:bg-[#F54963]/90 focus:ring-[#F54963]/40',
                ])
                ->action(fn () => $this->fillFakeData()),
            ...parent::getFormActions(),
        ];
    }

    protected function fillFakeData(): void
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
}
