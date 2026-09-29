<?php

namespace Leantime\Plugins\AiCommands\Services;

use Illuminate\Support\Facades\Http;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use RuntimeException;

class AiService
{
    public function __construct(private SettingService $settings) {}

    public function generate(string $text, string $operation): string
    {
        $baseUrl = rtrim((string) $this->settings->getSetting('aicommands.baseUrl', 'http://127.0.0.1:11434/v1'), '/');
        $model = (string) $this->settings->getSetting('aicommands.model', 'llama3.1');
        $apiKey = (string) $this->settings->getSetting('aicommands.apiKey', 'ollama');

        $instruction = match ($operation) {
            'user-story' => 'Rewrite the supplied notes as a concise user story in HTML. Use a heading and the format “As a …, I want …, so that …”, followed by acceptance criteria as a semantic unordered list when the source supports them. Do not invent requirements.',
            default => throw new RuntimeException('Unsupported AI operation.'),
        };

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->timeout(90)
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $instruction],
                    ['role' => 'user', 'content' => $text],
                ],
                'temperature' => 0.2,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('AI provider returned HTTP '.$response->status().'.');
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('AI provider returned an empty response.');
        }

        return trim($content);
    }
}
