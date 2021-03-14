<div class="modal fade" id="variants-matrix-{{ $product->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal inner-form" method="POST" action="{{ route('variants.updatematrix', $product->id) }}">
                <input type="hidden" name="close-modal" value="1">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Varianti') }}</h4>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php $combos = $product->variantCombos ?>

                            <table class="table">
                                <thead>
                                    <tr>
                                        @foreach($combos->first()->values as $value)
                                            <th>{{ $value->variant->name }}</th>
                                        @endforeach

                                        <th width="25%">
                                            Codice Fornitore
                                            @include('commons.helpbutton', [
                                                'help_popover' => _i("Se non viene specificato, tutte le varianti usano il Codice Fornitore del prodotto principale.")
                                            ])
                                        </th>
                                        <th width="25%">
                                            Differenza Prezzo
                                            @include('commons.helpbutton', [
                                                'help_popover' => _i("Differenza di prezzo, positiva o negativa, da applicare al prezzo del prodotto quando una specifica combinazione di varianti viene selezionata.")
                                            ])
                                        </th>

                                        @if ($product->measure->discrete)
                                            <th width="25%">Differenza Peso</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->variantCombos as $combo)
                                        <tr>
                                            @foreach($combo->values as $value)
                                                <td>{{ $value->value }}</td>
                                            @endforeach

                                            <td>
                                                <input type="hidden" name="combination[]" value="{{ $combo->values->pluck('id')->join(',') }}">

                                                @include('commons.textfield', [
                                                    'obj' => $combo,
                                                    'name' => 'code',
                                                    'postfix' => '[]',
                                                    'label' => _i('Codice Fornitore'),
                                                    'squeeze' => true
                                                ])
                                            </td>

                                            <td>
                                                @include('commons.decimalfield', [
                                                    'obj' => $combo,
                                                    'name' => 'price_offset',
                                                    'postfix' => '[]',
                                                    'label' => _i('Differenza Prezzo'),
                                                    'squeeze' => true,
                                                    'is_price' => true
                                                ])
                                            </td>

                                            @if ($product->measure->discrete)
                                                <td>
                                                    @include('commons.decimalfield', [
                                                        'obj' => $combo,
                                                        'name' => 'weight_offset',
                                                        'postfix' => '[]',
                                                        'label' => _i('Differenza Peso'),
                                                        'squeeze' => true,
                                                        'postlabel' => _i('Chili')
                                                    ])
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
