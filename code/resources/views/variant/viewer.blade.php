<?php $combos = $product->variant_combos ?>

@if($combos->isEmpty() == false)
    <hr>

    <div class="row">
        <div class="col">
            <table class="table">
                <thead>
                    <tr>
                        @foreach($combos->first()->values as $value)
                            <th scope="col">{{ $value->variant->name }}</th>
                        @endforeach

                        <th scope="col" width="25%">{{ __('texts.products.variant.price_difference') }}</th>

                        @if ($product->measure->discrete)
                            <th scope="col" width="25%">{{ __('texts.products.variant.weight_difference') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->sortedVariantCombos as $combo)
                        <tr>
                            @foreach($combo->values as $value)
                                <td>{{ $value->value }}</td>
                            @endforeach

                            <td>{{ printablePriceCurrency($combo->price_offset) }}</td>

                            @if ($product->measure->discrete)
                                <td>{{ $combo->weight_offset }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
