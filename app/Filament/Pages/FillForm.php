<?php

namespace App\Filament\Pages;

use App\Models\FormDefinition;
use App\Models\FormSubmission;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Contracts\HasNotifications;
use Filament\Notifications\Concerns\InteractsWithNotifications;
use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Http;

class FillForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.fill-form';

    public ?array $data = [];
    public ?int $selectedFormDefinitionId = null;

    public function mount(): void
    {
        $firstFormDefinition = FormDefinition::first();
        $this->selectedFormDefinitionId = $firstFormDefinition->id ?? null;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        $formDefinitions = FormDefinition::all()->pluck('name', 'id');

        $schema = [
            Select::make('selectedFormDefinitionId')
                ->label('Seleccionar formulario')
                ->options($formDefinitions)
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->selectedFormDefinitionId = $state),
        ];

        if ($this->selectedFormDefinitionId) {
            $formDefinition = FormDefinition::find($this->selectedFormDefinitionId);

            if ($formDefinition) {
                foreach ($formDefinition->fields as $field) {
                    $fieldComponent = null;

                    switch ($field['type']) {
                        case 'text':
                            $fieldComponent = TextInput::make($field['label']);
                            break;
                        case 'select':
                            $fieldComponent = Select::make($field['label'])->options(array_combine($field['options'], $field['options']));
                            break;
                        case 'checkbox':
                            $fieldComponent = Checkbox::make($field['label']);
                            break;
                        case 'radio':
                            $fieldComponent = Radio::make($field['label'])->options(array_combine($field['options'], $field['options']));
                            break;
                        case 'textarea':
                            $fieldComponent = Textarea::make($field['label']);
                            break;
                    }

                    if ($fieldComponent) {
                        $schema[] = $fieldComponent->label($field['label']);
                    }
                }
            }
        }

        return $schema;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->submit('saveToDb'),
        ];
    }

    public function saveToDb(): void
    {
        $data = $this->form->getState();

        FormSubmission::create([
            'form_definition_id' => $this->selectedFormDefinitionId,
            'data' => $data,
        ]);

        $this->form->fill();

        $this->notify('success', 'Formulario enviado correctamente.');
    }

    public function processWithChatGPT(): void
    {
        $data = $this->form->getState();

        // 1. Construct a prompt
        $prompt = "Here is some data from a form:\n\n";
        foreach ($data as $key => $value) {
            $prompt .= "- {$key}: {$value}\n";
        }
        $prompt .= "\nPlease process this data.";

        // 2. Call ChatGPT API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $chatGptResponse = $response->json('choices.0.message.content');

        // 3. Display response in a modal
        $this->dispatchBrowserEvent('open-chatgpt-modal', ['response' => $chatGptResponse]);
    }
}
