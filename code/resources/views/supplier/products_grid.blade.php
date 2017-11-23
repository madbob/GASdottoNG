<form class="inner-form" method="POST" action="{{ url('products/massiveupdate') }}">
    <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Unità di Misura</th>
                        <th>Prezzo Unitario</th>
                        <th>Prezzo Trasporto</th>
                        <th>Ordinabile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $measures = App\Measure::orderBy('name', 'asc')->get() ?>
                    @foreach($supplier->products as $product)
                        <tr>
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
                                    'label' => 'Nome',
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
                                    'label' => 'Unità di Misura',
                                    'squeeze' => true
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'price',
                                    'label' => 'Prezzo Unitario',
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
                                    'label' => 'Prezzo Trasporto',
                                    'squeeze' => true,
                                    'is_price' => true
                                ])
                            </td>
                            <td>
                                @include('commons.boolfield', [
                                    'obj' => $product,
                                    'prefix' => $product->id . '-',
                                    'name' => 'active',
                                    'label' => 'Ordinabile',
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
            <div class="btn-group pull-right" role="group">
                <button type="submit" class="btn btn-success saving-button">Salva</button>
            </div>
        </div>
    </div>
</form>
