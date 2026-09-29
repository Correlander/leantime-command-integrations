<?php

use Illuminate\Support\Facades\Route;
use Leantime\Plugins\AiCommands\Controllers\Ai;

Route::post('/aiCommands/ai/generate', [Ai::class, 'generate'])->name('aiCommands.ai.generate');
