<x-larastrap::modal :title="_i('Note e Quantità')" classes="order-fixes-modal" data-reload-url="{{ url('orders/fixes/' . $order->id . '/' . $product->id) }}">
    @php
    $bookings = $order->bookings()->sorted()->get();
    $measure = $product->printableMeasure();
    @endphp

    <x-larastrap::iform method="POST" :action="url('orders/fixes/' . $order->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit" />
        <input type="hidden" name="reload-portion" value=".order-summary-wrapper" class="skip-on-submit" />

        <input type="hidden" name="product" value="{{ $product->id }}" />

        @if($product->package_size != 0)
            <p>
                {{ _i('Dimensione Confezione') }}: {{ $product->package_size }} {{ $product->printableMeasure(true) }}
            </p>

            <hr/>
        @endif

        @if($product->global_min != 0)
            <p>
                {{ _i('Minimo Complessivo') }}: {{ $product->global_min }} {{ $product->measure->name }}
            </p>

            <hr/>
        @endif

        <x-larastrap::textarea name="notes" :label="_i('Note per il Fornitore')" rows="5" maxlength="500" :value="$product->pivot->notes" />

        <hr/>

        @if($bookings->isEmpty())
            <div class="alert alert-info">{{ _i("Da qui è possibile modificare la quantità prenotata di questo prodotto per ogni prenotazione, ma nessun utente ha ancora partecipato all'ordine.") }}</div>
        @else
            @if($product->variants()->count() == 0)
                <table class="table table-striped">
                    @foreach($bookings as $po)
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
                                    <div class="input-group-text">{{ $measure }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <x-larastrap::tabs use_anchors="true">
                    @foreach($product->variant_combos as $index => $combo)
                        <x-larastrap::tabpane :label="$combo->printableShortName()" :active="$index == 0">
                            <table class="table table-striped">
                                @foreach($bookings as $po)
                                    <tr>
                                        <td width="50%">
                                            <label>
                                                @if($po->user->isFriend())
                                                    {{ $po->user->parent->printableName() }}<br>
                                                    <small>Amico: {{ $po->user->printableName() }}</small>
                                                @else
                                                    {{ $po->user->printableName() }}
                                                @endif
                                            </label>
                                        </td>
                                        <td width="20%">
                                            @php

                                            $quantities = [];

                                            $pu = $po->getBooked($product);
                                            if ($pu) {
                                                $booked = $pu->getBookedCombos($combo);
                                                foreach($booked as $b) {
                                                    $quantities[] = $b->quantity;
                                                }
                                            }

                                            if (empty($quantities)) {
                                                $quantities[] = 0;
                                            }

                                            @endphp

                                            {!! join('<br>', array_map(function($q) use ($measure) {
                                                return sprintf('%s %s', $q, $measure);
                                            }, $quantities)) !!}
                                        </td>
                                        <td width="20%" class="text-end">
                                            <a href="#" data-modal-url="{{ $po->getModalURL() }}" class="btn btn-sm btn-warning async-modal">{{ _i('Modifica Prenotazione') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </x-larastrap::tabpane>
                    @endforeach
                </x-larastrap::tabs>
            @endif

            @if($order->aggregate->gas()->count() > 1 && $currentuser->can('gas.multi', $currentuser->gas))
                <table class="table table-striped mt-3">
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
                </table>
            @endif
        @endif
    </x-larastrap::iform>
</x-larastrap::modal>
