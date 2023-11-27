<?php

namespace App\Importers\CSV;

use DB;

use App\Supplier;
use App\Product;
use App\Category;
use App\Measure;
use App\VatRate;

class Products extends CSVImporter
{
    protected function fields()
    {
        return [
            'name' => (object) [
                'label' => _i('Nome'),
            ],
            'description' => (object) [
                'label' => _i('Descrizione')
            ],
            'price' => (object) [
                'label' => _i('Prezzo Unitario'),
            ],
            'price_without_vat' => (object) [
                'label' => _i('Prezzo Unitario (senza IVA)'),
                'explain' => _i('Da usare in combinazione con Aliquota IVA')
            ],
            'vat' => (object) [
                'label' => _i('Aliquota IVA'),
            ],
            'category' => (object) [
                'label' => _i('Categoria'),
            ],
            'measure' => (object) [
                'label' => _i('Unità di Misura'),
            ],
            'supplier_code' => (object) [
                'label' => _i('Codice Fornitore'),
            ],
            'package_size' => (object) [
                'label' => _i('Dimensione Confezione'),
            ],
            'package_price' => (object) [
                'label' => _i('Prezzo Confezione'),
                'explain' => _i('Se specificato, il prezzo unitario viene calcolato come Prezzo Confezione / Dimensione Confezione')
            ],
            'weight' => (object) [
                'label' => _i('Peso (in KG)'),
            ],
            'min_quantity' => (object) [
                'label' => _i('Ordine Minimo'),
            ],
            'multiple' => (object) [
                'label' => _i('Ordinabile per Multipli')
            ],
            'portion_quantity' => (object) [
                'label' => _i('Pezzatura')
            ],
        ];
    }

    private function getSupplier($request)
    {
        $supplier_id = $request->input('supplier_id');
        return Supplier::findOrFail($supplier_id);
    }

    public function testAccess($request)
    {
        return $request->user()->can('supplier.modify', $this->getSupplier($request));
    }

    public function guess($request)
    {
        $s = $this->getSupplier($request);

        return $this->storeUploadedFile($request, [
            'type' => 'products',
            'next_step' => 'select',
            'extra_fields' => ['supplier_id' => $s->id],
            'extra_description' => [_i('Le categorie e le unità di misura il cui nome non sarà trovato tra quelle esistenti saranno create.')],
            'sorting_fields' => $this->fields(),
        ]);
    }

    private function mapSelection($objects, $param, $value, $field, &$product)
    {
        $test = $objects->firstWhere($param, $value);
        if (is_null($test)) {
            return 'temp_' . $field . '_name';
        }
        else {
            $field_name = sprintf('%s_id', $field);
            $product->$field_name = $test->id;
            return null;
        }
    }

    public function select($request)
    {
        list($reader, $columns) = $this->initRead($request);
        list($name_index, $supplier_code_index) = $this->getColumnsIndex($columns, ['name', 'supplier_code']);
        $s = $this->getSupplier($request);

        $products = $errors = [];
        $all_products = $s->products;
        $all_categories = Category::all();
        $all_measures = Measure::all();
        $all_vatrates = VatRate::all();

        foreach($reader->getRecords() as $line) {
            if (empty($line) || (count($line) == 1 && empty($line[0]))) {
                continue;
            }

            try {
                $test = null;

                if ($supplier_code_index != -1 && filled($line[$supplier_code_index])) {
                    $test = $all_products->firstWhereAbout('supplier_code', $line[$supplier_code_index]);
                }

                if (is_null($test)) {
                    if ($name_index != -1 && filled($line[$name_index])) {
                        $test = $all_products->firstWhereAbout('name', $line[$name_index]);
                    }
                }

                if (is_null($test) == false) {
                    $p = $test;
                    $p->want_replace = $test;
                }
                else {
                    $p = new Product();
                    $p->category_id = Category::defaultValue();
                    $p->measure_id = Measure::defaultValue();
                    $p->weight = 0;
                    $p->min_quantity = 0;
                    $p->multiple = 0;
                    $p->package_size = 0;
                    $p->portion_quantity = 0;
                    $p->want_replace = null;
                    $price_without_vat = null;
                    $vat_rate = null;
                    $package_price = null;
                }

                foreach ($columns as $index => $field) {
                    $value = trim($line[$index]);

                    if ($field == 'category') {
                        $field = $this->mapSelection($all_categories, 'name', $value, 'category', $p);
                    }
                    elseif ($field == 'measure') {
                        $field = $this->mapSelection($all_measures, 'name', $value, 'measure', $p);
                    }
                    elseif ($field == 'price') {
                        $value = guessDecimal($value);
                    }
                    elseif ($field == 'vat') {
                        $value = guessDecimal($value);
                        if ($value == 0) {
                            $p->vat_rate_id = 0;
                            continue;
                        }
                        else {
                            $field = $this->mapSelection($all_vatrates, 'percentage', $value, 'vat_rate', $p);
                            $vat_rate = $value;
                        }
                    }
                    elseif ($field == 'price_without_vat' || $field == 'package_price') {
                        /*
                            Qui setto le variabili $price_without_vat o
                            $package_price, in funzione del valore stesso di
                            $field.
							Dunque $$field NON è un errore
                        */
                        $$field = guessDecimal($value);
                        continue;
                    }

                    if (!empty($value) && is_null($field) == false && $field != 'none') {
                        $p->$field = $value;
                    }
                }

                // @phpstan-ignore-next-line
                if (!empty($package_price) && !empty($p->package_size) && empty($p->price)) {
                    $p->price = $package_price / $p->package_size;
                }

                // @phpstan-ignore-next-line
                if (!empty($price_without_vat) && !empty($vat_rate)) {
                    $p->price = $price_without_vat + (($price_without_vat * $vat_rate) / 100);
                }

                $products[] = $p;
            }
            catch (\Exception $e) {
                $errors[] = join(',', $line) . '<br/>' . $e->getMessage();
            }
        }

        return ['products' => $products, 'supplier' => $s, 'errors' => $errors];
    }

    public function formatSelect($parameters)
    {
        return view('import.csvproductsselect', $parameters);
    }

    public function run($request)
    {
        DB::beginTransaction();

        $direct_fields = ['name', 'weight', 'description', 'price', 'supplier_code', 'package_size', 'min_quantity', 'multiple', 'portion_quantity'];
        $data = $request->all();

        $s = $this->getSupplier($request);
        $errors = $products = $products_ids = $new_categories = $new_measures = $new_vats = [];
        $service = app()->make('ProductsService');

        foreach($data['import'] as $index) {
            try {
                $fields = [];

                if (isset($data['want_replace'][$index]) && $data['want_replace'][$index] != '0') {
                    $product_id = $data['want_replace'][$index];
                    $ex_novo = false;
                }
                else {
                    $product_id = null;
                    $ex_novo = true;
                }

                $fields['supplier_id'] = $s->id;
                $fields['active'] = true;

                foreach($direct_fields as $field) {
                    $v = trim($data[$field][$index]);
                    if (filled($v)) {
                        $fields[$field] = $v;
                    }
                }

                $fields['category_id'] = $this->mapNewElements($data['category_id'][$index], $new_categories, function($name) {
                    return Category::easyCreate(['name' => $name]);
                });

                $fields['measure_id'] = $this->mapNewElements($data['measure_id'][$index], $new_measures, function($name) {
                    return Measure::easyCreate(['name' => $name]);
                });

                $fields['vat_rate_id'] = $this->mapNewElements($data['vat_rate_id'][$index], $new_vats, function($name) {
                    $name = (float) $name;
                    $vat = new VatRate();
                    $vat->percentage = $name;
                    $vat->name = sprintf('%f %%', round($name, 2));
                    $vat->save();
                    return $vat;
                });

                if ($ex_novo) {
                    $p = $service->store($fields);
                }
                else {
                    $p = $service->update($product_id, $fields);
                }

                $products[] = $p;
                $products_ids[] = $p->id;
            }
            catch (\Exception $e) {
                $errors[] = $index . '<br/>' . $e->getMessage();
            }
        }

        $reset_mode = $request->input('reset_list', 'no');
        switch($reset_mode) {
            case 'disable':
                $s->products()->whereNotIn('id', $products_ids)->update(['active' => false]);
                break;
        }

        DB::commit();

        return [
            'title' => _i('Prodotti importati'),
            'objects' => $products,
            'errors' => $errors,
            'extra_closing_attributes' => ['data-reload-target' => '#supplier-list']
        ];
    }
}
