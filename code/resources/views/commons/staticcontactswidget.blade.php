@foreach(($obj ? $obj->contacts : []) as $contact)
    @if(!empty($contact->value))
        <x-larastrap::field :label="$contact->type_name">
            <label class="static-label text-muted">
                @if($contact->type == 'website' && normalizeURL($contact->value))
                    <a href="{{ normalizeURL($contact->value) }}" target="_blank">{{ $contact->value }}</a>
                @else
                    {{ $contact->value }}
                @endif
            </label>
        </x-larastrap::field>
    @endif
@endforeach
