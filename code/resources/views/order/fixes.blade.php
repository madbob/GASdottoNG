<div class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal" method="POST" action="{{ url('orders/fixes/' . $order->id) }}">
                <input type="hidden" name="product" value="{{ $product->id }}" />

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Note e Quantità') }}</h4>
                </div>
                <div class="modal-body">
                    @if($product->package_size != 0)
                        <p>
                            {{ _i('Dimensione Confezione') }}: {{ $product->package_size }}
                        </p>

                        <hr/>
                    @endif

                    <div class="form-group">
                        <label for="notes" class="col-sm-{{ $labelsize }} control-label">{{ _i('Note per il Fornitore')}}</label>
                        <div class="col-sm-{{ $fieldsize }}">
                            <textarea class="form-control" name="notes" rows="5" autocomplete="off" maxlength="500">{{ $product->pivot->notes }}</textarea>
                        </div>
                    </div>

                    <hr/>

                    @if($order->bookings->isEmpty())
                        <div class="alert alert-info">{{ _i("Da qui è possibile modificare la quantità prenotata di questo prodotto per ogni prenotazione, ma nessun utente ha ancora partecipato all'ordine.") }}</div>
                    @else
                        <table class="table table-striped">
                            @foreach($order->bookings as $po)
                                <tr>
                                    <td>
                                        <label>
                                            @if($po->user->isFriend())
                                                {{ $po->user->parent->printableName() }}<br>
                                                <small>Amico: {{ $po->user->printableName() }}</small>
                                            @else
                                                {{ $po->user->printableName() }}
                                            @endif
                                        </label>
                                    </td>
                                    <td>
                                        <input type="hidden" name="booking[]" value="{{ $po->id }}" />

                                        <div class="input-group">
                                            <input type="text" class="form-control number" name="quantity[]" value="{{ $po->getBookedQuantity($product) }}" />
                                            <div class="input-group-addon">{{ $product->printableMeasure() }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @if($order->aggregate->gas()->count() > 1 && $currentuser->can('gas.multi', $currentuser->gas))
                                @foreach($order->aggregate->gas as $other_gas)
                                    @if($other_gas->id != $currentuser->gas->id)
                                        <tr>
                                            <td>
                                                <label>{{ _i('Multi-GAS: %s', [$other_gas->name]) }}</label>
                                            </td>
                                            <td>
                                                <label>
                                                    <?php

                                                    App::make('GlobalScopeHub')->setGas($other_gas->id);
                                                    $summary = $order->calculateSummary(collect([$product]));
                                                    $other_gas_quantity = $summary->products[$product->id]['quantity'];

                                                    ?>

                                                    {{ $other_gas_quantity }}
                                                </label>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-primary reloader" data-reload-target="#order-list">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
