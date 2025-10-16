@php
    $responses = $responses ?? [];
@endphp

<div class="space-y-4">
    @if (! empty($formName))
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ $formName }}
        </h4>
    @endif

    @if (empty($responses))
        <p class="text-sm text-gray-500 dark:text-gray-400">
            No hay respuestas guardadas todavía.
        </p>
    @else
        <dl class="space-y-3">
            @foreach ($responses as $response)
                <div class="rounded-xl border border-gray-200/70 bg-white/80 p-4 text-sm shadow-sm backdrop-blur dark:border-gray-700/60 dark:bg-gray-900/60">
                    <dt class="font-medium text-gray-800 dark:text-gray-100">
                        {{ $response['question'] ?? 'Pregunta' }}
                    </dt>
                    <dd class="mt-2 whitespace-pre-wrap text-gray-600 dark:text-gray-300">
                        {{ $response['answer'] ?? 'Sin respuesta' }}
                    </dd>
                </div>
            @endforeach
        </dl>
    @endif
</div>
