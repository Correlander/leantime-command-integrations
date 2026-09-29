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

    public function init(SettingService $settings): void
    {
        $this->settings = $settings;
    }

    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function get($params)
    {
        $this->tpl->assign('settings', [
            'baseUrl' => $this->settings->getSetting(self::KEYS['baseUrl'], 'http://127.0.0.1:11434/v1'),
            'model' => $this->settings->getSetting(self::KEYS['model'], 'llama3.1'),
            'apiKeyConfigured' => (string) $this->settings->getSetting(self::KEYS['apiKey'], '') !== '',
        ]);

        return $this->tpl->display('aicommands.settings');
    }

    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function post($params)
    {
        $validated = request()->validate([
            'baseUrl' => ['required', 'url', 'max:2048'],
            'model' => ['required', 'string', 'max:255'],
            'apiKey' => ['nullable', 'string', 'max:4096'],
        ]);

        $baseUrl = rtrim($validated['baseUrl'], '/');
        if (str_contains($baseUrl, '/chat/completions')) {
            return redirect(BASE_URL.'/AiCommands/settings')->withErrors(['baseUrl' => 'Enter the API base URL without /chat/completions.'])->withInput();
        }

        $this->settings->saveSetting(self::KEYS['baseUrl'], $baseUrl);
        $this->settings->saveSetting(self::KEYS['model'], trim($validated['model']));
        // A blank secret means keep the saved key; replace it by entering a new value.
        if (($validated['apiKey'] ?? '') !== '') {
            $this->settings->saveSetting(self::KEYS['apiKey'], $validated['apiKey']);
        }

        return redirect(BASE_URL.'/AiCommands/settings')->with('aiCommandsSaved', true);
    }
}
