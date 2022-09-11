<x-larastrap::modal :title="_i('Varianti')">
    <?php $combos = $product->variantCombos ?>

    <x-larastrap::form classes="inner-form" method="POST" :action="route('variants.updatematrix', $product->id)">
        <input type="hidden" name="close-modal" value="1">

        <div class="row">
            <div class="col">
                <table class="table">
                    @include('variant.matrixhead')

                    <tbody>
                        @foreach($product->sortedVariantCombos as $combo)
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
                                            <x-larastrap::number name="weight_offset" squeeze npostfix="[]" :textappend="_i('Chili')" />
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
