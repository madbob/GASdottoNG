@php

$enabled = [];

foreach(App\Gas::all() as $gas) {
    $enabled = array_merge($enabled, $gas->social_login);
}

$enabled = array_unique($enabled);

@endphp

@if(empty($enabled) == false)
    <hr />
    <div class="row mt-5">
        @foreach($enabled as $social)
            <div class="col">
                <div class="d-grid">
                    <a href="{{ route('login.social', $social) }}" class="btn btn-info">
                        <i class="bi bi-{{ $social }} me-5"></i>
                        {{ _i('Login con %s', [ucwords($social)]) }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
