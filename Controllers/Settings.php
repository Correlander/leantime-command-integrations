<?php

namespace Leantime\Plugins\AiCommands\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
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
        try {
            $this->settings = $settings;
        } catch (\Throwable $exception) {
            error_log('[AiCommands] Settings initialization failed: '.$exception);
            throw $exception;
        }
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
        try {
            return $this->saveSettings($params);
        } catch (\Throwable $exception) {
            error_log('[AiCommands] Saving settings failed: '.$exception);
            throw $exception;
        }
    }

    private function saveSettings($params)
    {
        // Leantime 3.10.0 replaces Laravel's translator binding, which makes
        // Request::validate() fail while resolving Laravel's validator.
        // Validate these small settings directly rather than using that helper.
        $request = request();
        $rawBaseUrl = $request->input('baseUrl');
        $rawModel = $request->input('model');
        $rawApiKey = $request->input('apiKey');

        $baseUrl = is_string($rawBaseUrl) ? rtrim(trim($rawBaseUrl), '/') : '';
        $model = is_string($rawModel) ? trim($rawModel) : '';
        $apiKey = is_string($rawApiKey) ? $rawApiKey : '';
        $this->formValues = ['baseUrl' => $baseUrl, 'model' => $model];

        if ($baseUrl === '' || strlen($baseUrl) > 2048 || filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            $this->settingsError = 'Enter a valid API base URL.';
            return $this->get($params);
        }

        if ($model === '' || strlen($model) > 255) {
            $this->settingsError = 'Enter a model name of 255 characters or fewer.';
            return $this->get($params);
        }

        if (! is_string($rawApiKey) && $rawApiKey !== null) {
            $this->settingsError = 'The API key must be plain text.';
            return $this->get($params);
        }

        if (strlen($apiKey) > 4096) {
            $this->settingsError = 'The API key must be 4096 characters or fewer.';
            return $this->get($params);
        }

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

        return redirect(BASE_URL.'/AiCommands/settings')->with('aiCommandsSaved', true);
    }
}
