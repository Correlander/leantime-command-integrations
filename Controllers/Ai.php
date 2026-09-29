<?php

namespace Leantime\Plugins\AiCommands\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Leantime\Plugins\AiCommands\Services\AiService;
use Throwable;

class Ai
{
    public function __construct(private AiService $aiService) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:50000'],
            'operation' => ['required', 'in:user-story'],
        ]);

        try {
            return response()->json([
                'content' => $this->aiService->generate($validated['text'], $validated['operation']),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['error' => 'The AI provider could not generate content.'], 502);
        }
    }
}
