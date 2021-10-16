<x-larastrap::modal :title="_i('Varianti')">
    <?php $combos = $product->variantCombos ?>

    @if($combos->count() == 0)
        <div class="alert alert-info">
            {{ _i('Questo prodotto non ha varianti. Definendo una o più varianti, da questo pannello sarà possibile specificare per ogni combinazione di valori una differenza prezzo, un codice fornitore distinto e altri attributi specifici.') }}
        </div>
    @else
        <x-larastrap::form classes="inner-form" method="POST" :action="route('variants.updatematrix', $product->id)">
            <input type="hidden" name="close-modal" value="1">

            <div class="row">
                <div class="col">
                    <table class="table">
                        <thead>
                            <tr>
                                @foreach($combos->first()->values as $value)
                                    <th>{{ $value->variant->name }}</th>
                                @endforeach

                                <th width="15%">{{ _i('Ordinabile') }}</th>

                                <th width="20%">
                                    {{ _i('Codice Fornitore') }}
                                    <x-larastrap::pophelp :text="_i('Se non viene specificato, tutte le varianti usano il Codice Fornitore del prodotto principale.')" />
                                </th>
                                <th width="20%">
                                    {{ _i('Differenza Prezzo') }}
                                    <x-larastrap::pophelp :text="_i('Differenza di prezzo, positiva o negativa, da applicare al prezzo del prodotto quando una specifica combinazione di varianti viene selezionata.')" />
                                </th>

                                @if($product->measure->discrete)
                                    <th width="20%">{{ _i('Differenza Peso') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variantCombos as $combo)
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
    @endif
</x-larastrap::modal>
