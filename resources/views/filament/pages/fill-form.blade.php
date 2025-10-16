<x-filament-panels::page>
    <form wire:submit.prevent="saveToDb">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </form>

    <div
        x-data="{ show: false, response: '' }"
        x-on:open-chatgpt-modal.window="show = true; response = $event.detail.response"
        x-show="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        style="display: none;"
    >
        <div class="bg-white p-8 rounded-lg shadow-lg" @click.away="show = false">
            <h2 class="text-lg font-bold mb-4">Respuesta de ChatGPT</h2>
            <div x-html="response" class="prose"></div>
            <button @click="show = false" class="mt-4 px-4 py-2 bg-gray-800 text-white rounded">Cerrar</button>
        </div>
    </div>
</x-filament-panels::page>
