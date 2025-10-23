<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateGptResponseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ask-gpt {prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ask ChatGPT a question and get a response.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prompt = $this->argument('prompt');

        $this->info('Sending prompt to ChatGPT...');

        try {
            $response = \OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $this->info('ChatGPT Response:');
            $this->comment($response->choices[0]->message->content);
        } catch (\Exception $e) {
            $this->error('Error communicating with OpenAI: ' . $e->getMessage());
        }
    }
}
