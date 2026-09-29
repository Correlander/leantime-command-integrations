@extends($layout)

@section('content')
    <div class="maincontent">
        <div class="maincontentinner">
            <h1>AI Commands Settings</h1>
            @if (!empty($settings['error']))
                <div class="alert alert-danger" role="alert">{{ $settings['error'] }}</div>
            @endif

            <form method="post" action="{{ BASE_URL }}/AiCommands/settings">
                @csrf
                <div class="row">
                    <div class="col-md-3"><label for="baseUrl">OpenAI-compatible API base URL</label></div>
                    <div class="col-md-7">
                        <input class="form-control" type="url" id="baseUrl" name="baseUrl" required maxlength="2048"
                               value="{{ $settings['baseUrl'] }}">
                        <small>For Ollama, this is usually http://127.0.0.1:11434/v1. Do not include /chat/completions.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3"><label for="model">Model</label></div>
                    <div class="col-md-7">
                        <input class="form-control" type="text" id="model" name="model" required maxlength="255"
                               value="{{ $settings['model'] }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3"><label for="apiKey">API key</label></div>
                    <div class="col-md-7">
                        @if ($settings['apiKeyConfigured'])
                            <div class="form-control" aria-label="API key is saved" style="margin-bottom: 8px;">••••••••••••••••</div>
                            <small>The key is saved and masked. Leave the field below blank to keep it, or enter a replacement.</small>
                        @else
                            <small>No key is saved. An API key may be optional for local Ollama.</small>
                        @endif
                        <input class="form-control" type="password" id="apiKey" name="apiKey" maxlength="4096"
                               autocomplete="new-password" placeholder="{{ $settings['apiKeyConfigured'] ? 'Enter a new key to replace the saved key' : 'Enter API key' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-7 col-md-offset-3">
                        <button class="btn btn-primary" type="submit">Save settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
