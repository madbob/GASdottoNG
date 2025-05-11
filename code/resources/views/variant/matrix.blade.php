<x-larastrap::modal>
    <?php

    /*
        Qui non usare $product->sortedVariantCombos perché ritorna solo le combo
        attualmente attive (mentre qui le voglio ovviamente tutte)
    */
    $combos = $product->variant_combos->sortBy(function($combo, $key) {
        return $combo->values->pluck('value')->join(' ');
    }, SORT_NATURAL);

    ?>

    <x-larastrap::form classes="inner-form" method="POST" :action="route('variants.updatematrix', $product->id)">
        <input type="hidden" name="close-modal" value="1">

        <div class="row">
            <div class="col">
                <table class="table">
                    @include('variant.matrixhead', [
                        'combos' => $combos,
                    ])

                    <tbody>
                        @foreach($combos as $combo)
                            <x-larastrap::enclose :obj="$combo">
                                <tr>
                                    @foreach($combo->values as $value)
                                        <td>{{ $value->value }}</td>
                                    @endforeach

                                    <td>
                                        <x-larastrap::check name="active" squeeze npostfix="[]" :value="$combo->id" :checked="$combo->active" />
                                    </td>

                                    <td>
                                        <input type="hidden" name="combination[]" value="{{ $combo->values->pluck('id')->join(',') }}">
                                        <x-larastrap::text name="code" squeeze npostfix="[]" />
                                    </td>

                                    <td>
                                        <x-larastrap::price name="price_offset" squeeze npostfix="[]" />
                                    </td>

                                    @if ($product->measure->discrete)
                                        <td>
                                            <x-larastrap::number name="weight_offset" squeeze npostfix="[]" ttextappend="generic.kilos" step="0.01" />
                                        </td>
                                    @endif
                                </tr>
                            </x-larastrap::enclose>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
