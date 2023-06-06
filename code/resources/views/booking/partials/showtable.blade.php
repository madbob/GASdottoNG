{{--
    La visualizzazione non editabile della prenotazione può essere usata in
    molteplici contesti, motivo per cui viene messa qui
--}}

@if($o->products->isEmpty())
    <div class="alert alert-info mb-4">
        {{ _i("Non hai partecipato a quest'ordine.") }}
    </div>
@else
    <x-larastrap::hidden name="skip_order[]" :value="$order->id" />

    <table class="table table-striped booking-editor" id="booking_{{ sanitizeId($order->id) }}">
        <thead class="d-none d-md-table-header-group">
            <tr>
                <th width="50%">{{ _i('Prodotto') }}</th>
                <th width="20%">{{ _i('Ordinato') }}</th>
                <th width="20%">{{ _i('Consegnato') }}</th>
                <th width="10%" class="text-end">{{ _i('Totale Prezzo') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($o->products as $product)
                @if($product->variants->isEmpty() == true)
                    <tr>
                        <td>
                            @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product->product])

                            <div class="d-none">
                                @foreach($product->product->icons() as $icon)
                                    <i class="bi-{{ $icon }}"></i>
                                @endforeach
                            </div>
                        </td>

                        <td>
                            {{ printableQuantity($product->quantity, $product->product->measure->discrete) }} {{ $product->product->printableMeasure(true) }}
                        </td>

                        <td>
                            {{ printableQuantity($product->delivered, $product->product->measure->discrete, 3) }} {{ $product->product->measure->name }}
                        </td>

                        <td>
                            <label class="float-end">
                                {{ printablePriceCurrency($product->getValue('effective')) }}
                            </label>
                        </td>
                    </tr>
                @else
                    @foreach($product->variants as $var)
                        <tr>
                            <td>
                                <x-larastrap::field squeeze="true">
                                    <label class="static-label">
                                        {{ $product->product->name }}: {{ $var->printableName() }}
                                    </label>

                                    <div class="float-end">
                                        @include('commons.detailsbutton', ['obj' => $product->product])
                                    </div>
                                </x-larastrap::field>
                            </td>

                            <td>
                                {{ printableQuantity($var->quantity, $product->product->measure->discrete) }} {{ $product->product->printableMeasure(true) }}
                            </td>

                            <td>
                                {{ printableQuantity($var->delivered, $product->product->measure->discrete, 3) }} {{ $product->product->measure->name }}
                            </td>

                            <td>
                                <label class="float-end">
                                    {{ printablePriceCurrency($order->isActive() ? $var->quantityValue() : $var->deliveredValue()) }}
                                </label>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach

            @foreach($mods as $mod_value)
                @include('delivery.modifierrow', [
                    'mod_value' => $mod_value,
                    'skip_cells' => 2,
                    'final_value' => true,
                ])
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th class="text-end">{{ _i('Totale') }}: <span class="booking-total">{{ printablePrice($o->getValue('effective', false)) }}</span> {{ $currentgas->currency }}</th>
            </tr>
        </tfoot>
    </table>

    @if(!empty($o->notes))
        <div class="row">
            <div class="col-md-12">
                <x-larastrap::text :obj="$o" name="notes" :label="_i('Note')" readonly disabled />
            </div>
        </div>
    @endif
@endif
