@extends($layout)

@section('content')
    <div class="maincontent">
        <div class="maincontentinner">
            <form method="post" action="{{ BASE_URL }}/AiCommands/settings">
                @csrf
                {!! $settingsContent !!}
                <button class="btn btn-primary" type="submit">Save settings</button>
            </form>
        </div>
    </div>
@endsection
