# AI Commands

A proof-of-concept Leantime plugin that registers `/ai` in the Tiptap editor and turns the current editor text into a user story through an OpenAI-compatible Chat Completions API. The default target is local Ollama.

## Install

Copy this folder to Leantime's `app/Plugins/AiCommands` directory and enable **AI Commands** from the plugin manager. Once enabled, use its **Settings** link to configure the provider base URL, model, and API key. These values are saved in Leantime's settings store; no Leantime `.env` changes are required.

The base URL is the API base URL (without `/chat/completions`). The key is read server-side and never sent to the browser; leaving its field blank preserves an existing key.

The provider must be reachable from the Leantime PHP runtime. The default Ollama URL uses `127.0.0.1`, so Ollama must be reachable at that address from the Leantime server.

## Files

- `register.php` registers the script in Leantime's header through `Registration::addHeaderJs()`. This lets the slash command register before Tiptap editors initialize.
- `routes.php` adds the authenticated generation endpoint.
- `Controllers/Ai.php` validates requests with Leantime 3.10.0's `ValidationException::validate()` bridge and returns JSON.
- `Controllers/Settings.php` provides the plugin manager's settings page and uses Leantime 3.10.0's `ValidationException::validate()` bridge for field validation.
- `Templates/settings.blade.php` contains the editable configuration form.
- `Services/AiService.php` calls the configurable OpenAI-compatible endpoint.
- `Assets/js/ai-commands.js` is the editable source for the `/ai` slash command.
- `dist/ai-commands.js` and `dist/mix-manifest.json` are the packaged asset files Leantime's `addHeaderJs()` registration reads.

## Plugin folder name

Install the plugin files in a folder named exactly `AiCommands` under Leantime's `app/Plugins/` directory. The final path should be `app/Plugins/AiCommands/`. Leantime 3.10.0 derives the plugin lifecycle service and controller namespaces from this folder name, so do not use the repository folder name `Leantime Commands Integrations` for the installed plugin directory.

Slash commands are available in Leantime's complex and notes Tiptap editors. The simple comment editor disables slash commands in Leantime 3.10.0.
