<form class="form-horizontal main-form receipt-editor" method="PUT" action="{{ route('receipts.update', $receipt->id) }}">
    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $receipt, 'name' => 'user', 'label' => _i('Utente')])
            @include('commons.staticstringfield', ['obj' => $receipt, 'name' => 'number', 'label' => _i('Numero')])
            @include('commons.datefield', ['obj' => $receipt, 'name' => 'date', 'label' => _i('Data')])
            @include('commons.staticpricefield', ['obj' => $receipt, 'name' => 'total', 'label' => _i('Totale Imponibile')])
            @include('commons.staticpricefield', ['obj' => $receipt, 'name' => 'total_vat', 'label' => _i('Totale IVA')])
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="bookings" class="col-sm-{{ $labelsize }} control-label">{{ _i('Prenotazioni Coinvolte') }}</label>

                <div class="col-sm-{{ $fieldsize }}">
                    @foreach($receipt->bookings as $booking)
                        <div class="row">
                            <div class="col-md-12">
                                <label class="static-label text-muted">
                                    {{ $booking->printableName() }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="list-group pull-right">
                <a href="#" class="list-group-item" data-toggle="modal" data-target="#receipt-document-{{ $receipt->id }}">{{ _i('Scarica o Inoltra') }}</a>
            </div>
        </div>
    </div>

    @include('commons.formbuttons')
</form>

<div class="modal fade close-on-submit order-document-download-modal" id="receipt-document-{{ $receipt->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ route('receipts.download', $receipt->id) }}" data-toggle="validator" novalidate>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Scarica o Inoltra') }}</h4>
                </div>
                <div class="modal-body">
                    <p>
                        {{ _i("Scarica la fattura generata, o inoltrala via email.") }}
                    </p>

                    @include('order.filesmail', [
                        'contacts' => $receipt->user->contacts()->where('type', 'email')->get(),
                        'default_subject' => _i('Nuova fattura da %s', [$currentgas->name]),
                        'default_text' => _i("In allegato l'ultima fattura da %s.", [$currentgas->name])
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
