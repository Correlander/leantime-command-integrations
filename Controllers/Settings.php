<?php

namespace Leantime\Plugins\AiCommands\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Plugins\Permissions\PluginsPermissions;
use Leantime\Domain\Setting\Services\Setting as SettingService;

class Settings extends Controller
{
    private const KEYS = [
        'baseUrl' => 'aicommands.baseUrl',
        'model' => 'aicommands.model',
        'apiKey' => 'aicommands.apiKey',
    ];

    private SettingService $settings;

    private ?string $settingsError = null;

    private array $formValues = [];

    public function init(SettingService $settings): void
    {
        $this->settings = $settings;
    }

    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function get($params)
    {
        $this->tpl->assign('settings', [
            'baseUrl' => $this->formValues['baseUrl'] ?? $this->settings->getSetting(self::KEYS['baseUrl'], 'http://127.0.0.1:11434/v1'),
            'model' => $this->formValues['model'] ?? $this->settings->getSetting(self::KEYS['model'], 'llama3.1'),
            'apiKeyConfigured' => (string) $this->settings->getSetting(self::KEYS['apiKey'], '') !== '',
            'error' => $this->settingsError,
        ]);

        return $this->tpl->display('aicommands.settings');
    }

    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function post($params)
    {
        return $this->saveSettings($params);
    }

    private function saveSettings($params)
    {
        $input = $this->incomingRequest->only(['baseUrl', 'model', 'apiKey']);
        $this->formValues = [
            'baseUrl' => is_string($input['baseUrl'] ?? null) ? trim($input['baseUrl']) : '',
            'model' => is_string($input['model'] ?? null) ? trim($input['model']) : '',
        ];

        try {
            $validated = ValidationException::validate($input, [
                'baseUrl' => ['required', 'string', 'url', 'max:2048'],
                'model' => ['required', 'string', 'max:255'],
                'apiKey' => ['nullable', 'string', 'max:4096'],
            ], [
                'baseUrl.required' => 'Enter an API base URL.',
                'baseUrl.string' => 'The API base URL must be text.',
                'baseUrl.url' => 'Enter a valid API base URL.',
                'baseUrl.max' => 'The API base URL must be 2048 characters or fewer.',
                'model.required' => 'Enter a model name.',
                'model.string' => 'The model name must be text.',
                'model.max' => 'The model name must be 255 characters or fewer.',
                'apiKey.string' => 'The API key must be text.',
                'apiKey.max' => 'The API key must be 4096 characters or fewer.',
            ]);
        } catch (ValidationException $exception) {
            $fieldErrors = $exception->getErrorData();
            $firstFieldErrors = reset($fieldErrors);
            $this->settingsError = is_array($firstFieldErrors) ? (string) reset($firstFieldErrors) : 'Check the settings and try again.';

            return $this->get($params);
        }

        $baseUrl = rtrim(trim($validated['baseUrl']), '/');
        $model = trim($validated['model']);
        $apiKey = $validated['apiKey'] ?? '';

        if (str_contains($baseUrl, '/chat/completions')) {
            $this->settingsError = 'Enter the API base URL without /chat/completions.';
            return $this->get($params);
        }

        $this->settings->saveSetting(self::KEYS['baseUrl'], $baseUrl);
        $this->settings->saveSetting(self::KEYS['model'], $model);
        // A blank secret means keep the saved key; replace it by entering a new value.
        if (trim($apiKey) !== '') {
            $this->settings->saveSetting(self::KEYS['apiKey'], $apiKey);
        }

        $this->tpl->setNotification('AI Commands settings saved.', 'success');

        return Frontcontroller::redirect(BASE_URL.'/AiCommands/settings');
    }
}
