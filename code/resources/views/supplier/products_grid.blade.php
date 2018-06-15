<form class="inner-form" method="POST" action="{{ url('products/massiveupdate') }}">
    <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>{{ _i('Nome') }}</th>
                        <th>{{ _i('Unità di Misura') }}</th>
                        <th>{{ _i('Prezzo Unitario') }}</th>
                        <th>{{ _i('Prezzo Trasporto') }}</th>
                        <th>{{ _i('Ordinabile') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $measures = App\Measure::orderBy('name', 'asc')->get() ?>
                    @foreach($supplier->products as $product)
                        <tr>
                            <td>
                                <input type="checkbox" name="printable[]" value="{{ $product->id }}" {{ $product->active ? 'checked' : '' }}>
                            </td>
                            <td>
                                @include('commons.hiddenfield', [
                                    'obj' => $product,
                                    'name' => 'id',
                                    'postfix' => '[]'
                                ])

                                @include('commons.textfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'name',
                                    'label' => _i('Nome'),
                                    'squeeze' => true,
                                    'mandatory' => true
                                ])
                            </td>
                            <td>
                                @include('commons.selectobjfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'measure_id',
                                    'objects' => $measures,
                                    'label' => _i('Unità di Misura'),
                                    'squeeze' => true
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'price',
                                    'label' => _i('Prezzo Unitario'),
                                    'squeeze' => true,
                                    'is_price' => true,
                                    'mandatory' => true
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'transport',
                                    'label' => _i('Prezzo Trasporto'),
                                    'squeeze' => true,
                                    'is_price' => true
                                ])
                            </td>
                            <td>
                                @include('commons.boolfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'active',
                                    'label' => _i('Ordinabile'),
                                    'squeeze' => true
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-default export-custom-products-list" data-export-url="{{ url('suppliers/catalogue/' . $supplier->id . '/pdf') }}">{{ _i('Listino PDF Prodotti Selezionati') }}</button>
            <button class="btn btn-default export-custom-products-list" data-export-url="{{ url('suppliers/catalogue/' . $supplier->id . '/csv') }}">{{ _i('Listino CSV Prodotti Selezionati') }}</button>

            <div class="btn-group pull-right" role="group">
                <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
            </div>
        </div>
    </div>
</form>
