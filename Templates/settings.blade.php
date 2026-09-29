@extends($layout)

@section('content')
    <div class="maincontent">
        <div class="maincontentinner">
            <h1>AI Commands Settings</h1>
            @if (session('aiCommandsSaved'))
                <div class="alert alert-success" role="status">Settings saved.</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ BASE_URL }}/AiCommands/settings">
                @csrf
                <div class="row">
                    <div class="col-md-3"><label for="baseUrl">OpenAI-compatible API base URL</label></div>
                    <div class="col-md-7">
                        <input class="form-control" type="url" id="baseUrl" name="baseUrl" required maxlength="2048"
                               value="{{ old('baseUrl', $settings['baseUrl']) }}">
                        <small>For Ollama, this is usually http://127.0.0.1:11434/v1. Do not include /chat/completions.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3"><label for="model">Model</label></div>
                    <div class="col-md-7">
                        <input class="form-control" type="text" id="model" name="model" required maxlength="255"
                               value="{{ old('model', $settings['model']) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3"><label for="apiKey">API key</label></div>
                    <div class="col-md-7">
                        <input class="form-control" type="password" id="apiKey" name="apiKey" maxlength="4096"
                               autocomplete="new-password" placeholder="{{ $settings['apiKeyConfigured'] ? 'Saved; leave blank to keep it' : 'Optional for local Ollama' }}">
                        <small>The saved key is never displayed. Enter a new value to replace it.</small>
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
