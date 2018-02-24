<form class="form-horizontal main-form attachment-editor" method="PUT" action="{{ route('attachments.update', $attachment->id) }}">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="download" class="col-sm-{{ $labelsize }} control-label">{{ _i('Scarica') }}</label>

                <div class="col-sm-{{ $fieldsize }}">
                    <a class="btn btn-default" href="{{ url('attachments/download/' . $attachment->id) }}">{{ _i('Clicca Qui') }}</a>
                </div>
            </div>
        </div>
    </div>
</form>
