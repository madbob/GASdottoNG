<?php

$original_products = $supplier->products;
$categories = App\Category::orderBy('name', 'asc')->where('parent_id', '=', null)->get();
$measures = App\Measure::orderBy('name', 'asc')->get();
$vat_rates = App\VatRate::orderBy('percentage', 'asc')->get();

?>

<div class="wizard_page">
    @if(!empty($errors))
        <div class="alert alert-danger">
            <ul>
                @foreach($errors as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=products&step=run') }}" data-toggle="validator">
        <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    @include('commons.boolfield', [
                        'obj' => null,
                        'name' => 'reset_list',
                        'label' => _i("Disattiva prodotti di questo fornitore non inclusi nell'elenco"),
                        'default_checked' => false
                    ])
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th width="3%">{{ _i('Importa') }}</th>
                        <th width="15%">{{ _i('Nome') }}</th>
                        <th width="15%">{{ _i('Descrizione') }}</th>
                        <th width="8%">{{ _i('Prezzo Unitario') }}</th>
                        <th width="8%">{{ _i('Categoria') }}</th>
                        <th width="8%">{{ _i('Unità di Misura') }}</th>
                        <th width="8%">{{ _i('Aliquota IVA') }}</th>
                        <th width="8%">{{ _i('Codice Fornitore') }}</th>
                        <th width="6%">{{ _i('Dimensione Confezione') }}</th>
                        <th width="6%">{{ _i('Ordine Minimo') }}</th>
                        <th width="6%">{{ _i('Ordinabile per Multipli') }}</th>
                        <th width="9%">{{ _i('Aggiorna') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                        <tr>
                            <td>
                                <input type="checkbox" name="import[]" value="{{ $index }}" checked>
                            </td>
                            <td>
                                @include('commons.textfield', [
                                    'obj' => $product,
                                    'name' => 'name',
                                    'label' => '',
                                    'postfix' => '[]',
                                    'squeeze' => true
                                ])
                            </td>
                            <td>
                                @include('commons.textfield', [
                                    'obj' => $product,
                                    'name' => 'description',
                                    'label' => '',
                                    'postfix' => '[]',
                                    'squeeze' => true
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'name' => 'price',
                                    'label' => '',
                                    'postfix' => '[]',
                                    'squeeze' => true,
                                ])
                            </td>
                            <td>
                                @include('commons.selectobjfield', [
                                    'obj' => $product,
                                    'name' => 'category_id',
                                    'postfix' => '[]',
                                    'squeeze' => true,
                                    'objects' => $categories,
                                    'extra_selection' => (isset($product->temp_category_name) ? ['new:' . $product->temp_category_name => $product->temp_category_name] : [])
                                ])
                            </td>
                            <td>
                                @include('commons.selectobjfield', [
                                    'obj' => $product,
                                    'name' => 'measure_id',
                                    'postfix' => '[]',
                                    'squeeze' => true,
                                    'objects' => $measures,
                                    'extra_selection' => (isset($product->temp_measure_name) ? ['new:' . $product->temp_measure_name => $product->temp_measure_name] : [])
                                ])
                            </td>
                            <td>
                                @include('commons.selectobjfield', [
                                    'obj' => $product,
                                    'name' => 'vat_rate_id',
                                    'postfix' => '[]',
                                    'squeeze' => true,
                                    'objects' => $vat_rates,
                                    'extra_selection' => (isset($product->temp_vat_rate_name) ? ['new:' . $product->temp_vat_rate_name => $product->temp_vat_rate_name] : [])
                                ])
                            </td>
                            <td>
                                @include('commons.textfield', [
                                    'obj' => $product,
                                    'name' => 'supplier_code',
                                    'label' => '',
                                    'postfix' => '[]',
                                    'squeeze' => true
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'name' => 'package_size',
                                    'label' => '',
                                    'postfix' => '[]',
                                    'squeeze' => true,
                                    'decimals' => 3
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'name' => 'min_quantity',
                                    'label' => '',
                                    'postfix' => '[]',
                                    'squeeze' => true,
                                    'decimals' => 3
                                ])
                            </td>
                            <td>
                                @include('commons.decimalfield', [
                                    'obj' => $product,
                                    'name' => 'multiple',
                                    'label' => '',
                                    'postfix' => '[]',
                                    'squeeze' => true,
                                    'decimals' => 3
                                ])
                            </td>

                            <td>
                                @if($original_products->isEmpty() == false)
                                    @include('commons.selectobjfield', [
                                        'obj' => $product,
                                        'name' => 'want_replace',
                                        'postfix' => '[]',
                                        'squeeze' => true,
                                        'objects' => $original_products,
                                        'extra_selection' => [
                                            '-1' => _i('Nessuno')
                                        ]
                                    ])
                                @else
                                    {{ _i('Nessun Prodotto Aggiornabile') }}
                                    <input type="hidden" name="want_replace[]" value="-1">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
            <button type="submit" class="btn btn-success">{{ _i('Avanti') }}</button>
        </div>
    </form>
</div>
