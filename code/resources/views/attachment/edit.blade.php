<form class="form-horizontal main-form attachment-editor" method="PUT" action="{{ route('attachments.update', $attachment->id) }}" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12">
            @if($attachment->internal == false)
                @include('commons.textfield', ['obj' => $attachment, 'name' => 'name', 'label' => _i('Nome')])
                @include('commons.filefield', ['obj' => $attachment, 'name' => 'file', 'label' => _i('Sostituisci File')])
            @else
                @include('commons.staticstringfield', ['obj' => $attachment, 'name' => 'name', 'label' => _i('Nome')])
            @endif

            @include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => _i('Destinatari')])

            <div class="form-group">
                <label for="download" class="col-sm-{{ $labelsize }} control-label">{{ _i('Scarica')}}</label>

                <div class="col-sm-{{ $fieldsize }}">
                    @if($attachment->isImage())
                        <img src="{{ $attachment->download_url }}" class="img-responsive">
                    @else
                        <a class="btn btn-default" href="{{ $attachment->download_url }}">{{ _i('Clicca Qui') }} <span class="glyphicon glyphicon-download" aria-hidden="true"></span></a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('commons.formbuttons', ['no_delete' => $attachment->internal, 'no_save' => $attachment->internal])
</form>
