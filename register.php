<?php

use Leantime\Domain\Plugins\Services\Registration;

$registration = app()->makeWith(Registration::class, ['pluginId' => 'AiCommands']);
$registration->addFooterJs(['ai-commands.js']);
