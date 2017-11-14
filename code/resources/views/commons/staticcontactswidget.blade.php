@foreach(($obj ? $obj->contacts : []) as $contact)
    @if(!empty($contact->value))
        <div class="form-group">
            <label class="col-sm-{{ $labelsize }} control-label">{{ $contact->type_name }}</label>
            <div class="col-sm-{{ $fieldsize }}">
                <label class="static-label text-muted">
                    @if($contact->type == 'website' && normalizeURL($contact->value))
                        <a href="{{ normalizeURL($contact->value) }}" target="_blank">{{ $contact->value }}</a>
                    @else
                        {{ $contact->value }}
                    @endif
                </label>
            </div>
        </div>
    @endif
@endforeach
