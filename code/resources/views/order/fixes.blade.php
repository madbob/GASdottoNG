<x-larastrap::modal :title="_i('Note e Quantità')">
    <x-larastrap::form method="POST" :action="url('orders/fixes/' . $order->id)">
        <input type="hidden" name="product" value="{{ $product->id }}" />

        @if($product->package_size != 0)
            <p>
                {{ _i('Dimensione Confezione') }}: {{ $product->package_size }} {{ $product->printableMeasure() }}
            </p>

            <hr/>
        @endif

        <x-larastrap::textarea name="notes" :label="_i('Note per il Fornitore')" rows="5" maxlength="500" :value="$product->pivot->notes" />

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
                                <div class="input-group-text">{{ $product->printableMeasure() }}</div>
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
                                        $summary = $order->reduxData();
                                        $other_gas_quantity = $summary->products[$product->id]->quantity;

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
    </x-larastrap::form>
</x-larastrap::modal>
