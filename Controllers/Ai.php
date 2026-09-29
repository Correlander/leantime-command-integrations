<?php

namespace Leantime\Plugins\AiCommands\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Plugins\AiCommands\Services\AiService;
use Throwable;

class Ai
{
    public function __construct(private AiService $aiService) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = ValidationException::validate($request->only(['text', 'operation']), [
            'text' => ['required', 'string', 'max:50000'],
            'operation' => ['required', 'in:user-story'],
        ], [
            'text.required' => 'Provide text for the AI command.',
            'text.string' => 'The AI command text must be plain text.',
            'text.max' => 'The AI command text must be 50000 characters or fewer.',
            'operation.required' => 'Choose an AI operation.',
            'operation.in' => 'The requested AI operation is not supported.',
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
