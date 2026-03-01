@php

$contacts = ($obj ? $obj->contacts : new Illuminate\Support\Collection());

@endphp

@if($contacts->isEmpty() == false)
    <x-ls::card header="generic.contacts">
        @foreach($contacts as $contact)
            @if(!empty($contact->value))
                <x-larastrap::field :label="$contact->type_name">
                    <span class="static-label">
                        @if($contact->type == 'website' && normalizeURL($contact->value))
                            <a href="{{ normalizeURL($contact->value) }}" target="_blank">{{ $contact->value }}</a>
                        @elseif($contact->type == 'phone' || $contact->type == 'mobile')
                            <a href="tel:{{ trim($contact->value) }}">{{ $contact->value }}</a>
                        @elseif($contact->type == 'email')
                            <a href="mailto:{{ trim($contact->value) }}" target="_blank">{{ $contact->value }}</a>
                        @elseif($contact->type == 'address')
                            {{ $contact->asFlatAddress() }}
                        @else
                            {{ $contact->value }}
                        @endif
                    </span>
                </x-larastrap::field>
            @endif
        @endforeach
    </x-ls::card>
@endif
