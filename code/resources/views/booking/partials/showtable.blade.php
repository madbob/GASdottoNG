{{--
    La visualizzazione non editabile della prenotazione può essere usata in
    molteplici contesti, motivo per cui viene messa qui
--}}

@if($o->products->isEmpty())
    <div class="alert alert-info mb-4">
        {{ _i("Non hai partecipato a quest'ordine.") }}
    </div>
@else
    @php
    $categories = $o->products->reduce(fn($carry, $p) => $carry->push($p->product->category), new \Illuminate\Support\Collection())->unique();
    @endphp

    <x-larastrap::hidden name="skip_order[]" :value="$order->id" />

    <table class="table table-striped booking-editor" id="booking_{{ sanitizeId($order->id) }}">
        <thead class="d-none d-md-table-header-group">
            <tr>
                <th width="40%"></th>
                <th width="27%"></th>
                <th width="27%"></th>
                <th width="5%"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
                <tr class="table-sorting-header d-none" data-sorting-category_name="{{ $cat->name }}">
                    <td colspan="4">
                        {{ $cat->name }}
                    </td>
                </tr>
            @endforeach

            @foreach($o->products as $product)
                @if($product->variants->isEmpty() == true)
                    <tr data-sorting-name="{{ $product->product->name }}" data-sorting-sorting="{{ $product->product->sorting }}" data-sorting-category_name="{{ $product->product->category_name }}">
                        <td>
                            @include('commons.staticobjfield', ['squeeze' => true, 'target_obj' => $product->product, 'extra_class' => 'text-filterable-cell'])

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
                        <tr data-sorting-name="{{ $product->product->name }}" data-sorting-sorting="{{ $product->product->sorting }}" data-sorting-category_name="{{ $product->product->category_name }}">
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
                <th class="text-end">{{ _i('Totale') }}:<br><span class="booking-total">{{ printablePrice($o->getValue('effective', false)) }}</span> {{ defaultCurrency()->symbol }}</th>
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
