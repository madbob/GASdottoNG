<form class="form-horizontal main-form receipt-editor">
    <div class="row">
        <div class="col-md-6">
            @include('commons.staticobjfield', ['obj' => $receipt, 'name' => 'user', 'label' => _i('Utente')])
            @include('commons.staticstringfield', ['obj' => $receipt, 'name' => 'number', 'label' => _i('Numero')])
            @include('commons.staticpricefield', ['obj' => $receipt, 'name' => 'total', 'label' => _i('Totale Imponibile')])
            @include('commons.staticpricefield', ['obj' => $receipt, 'name' => 'total_vat', 'label' => _i('Totale IVA')])
        </div>
        <div class="col-md-6">
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
    </div>

    @include('commons.formbuttons', [
        'no_delete' => true,
        'no_save' => true,
        'left_buttons' => [
            (object)[
                'url' => route('receipt.download', $receipt->id),
                'class' => '',
                'label' => _i('Scarica')
            ]
        ]
    ])
</form>
