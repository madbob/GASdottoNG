<?php

namespace App\Importers\CSV;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Supplier;
use App\Product;
use App\Category;
use App\Measure;
use App\VatRate;

class Products extends CSVImporter
{
    public function fields()
    {
        return [
            'name' => (object) [
                'label' => __('texts.generic.name'),
            ],
            'description' => (object) [
                'label' => __('texts.generic.description'),
            ],
            'price' => (object) [
                'label' => __('texts.products.prices.unit'),
            ],
            'price_without_vat' => (object) [
                'label' => __('texts.products.prices.unit_no_vat'),
                'explain' => __('texts.products.help.unit_no_vat'),
            ],
            'vat' => (object) [
                'label' => __('texts.products.vat_rate'),
            ],
            'category' => (object) [
                'label' => __('texts.generic.category'),
            ],
            'measure' => (object) [
                'label' => __('texts.generic.measure'),
            ],
            'supplier_code' => (object) [
                'label' => __('texts.products.code'),
            ],
            'package_size' => (object) [
                'label' => __('texts.products.package_size'),
            ],
            'package_price' => (object) [
                'label' => __('texts.products.prices.package'),
                'explain' => __('texts.products.help.package_price'),
            ],
            'weight' => (object) [
                'label' => __('texts.products.weight_with_measure'),
            ],
            'min_quantity' => (object) [
                'label' => __('texts.products.min_quantity'),
            ],
            'multiple' => (object) [
                'label' => __('texts.products.multiple'),
            ],
            'portion_quantity' => (object) [
                'label' => __('texts.products.portion_quantity'),
            ],
        ];
    }

    private function getSupplier(array $request)
    {
        $supplier_id = $request['supplier_id'];
        return Supplier::findOrFail($supplier_id);
    }

    public function testAccess(array $request)
    {
        return Auth::user()->can('supplier.modify', $this->getSupplier($request));
    }

    public function guess(array $request)
    {
        $s = $this->getSupplier($request);

        return $this->storeUploadedFile($request, [
            'type' => 'products',
            'next_step' => 'select',
            'extra_fields' => ['supplier_id' => $s->id],
            'extra_description' => [__('texts.products.help.importing_categories_and_measures')],
            'sorting_fields' => $this->fields(),
            'sorted_fields' => json_decode($s->import_template),
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

    private function findExistingProduct($line, $products, $params)
    {
        foreach($params as $name => $index) {
            if ($index != -1 && filled($line[$index])) {
                $test = $products->firstWhereAbout($name, $line[$index]);
                if ($test) {
                    return $test;
                }
            }
        }

        return null;
    }

    private function fixCalculatedPrice($p, $package_price, $price_without_vat, $vat_rate)
    {
        $price = $p->price;

        if (! empty($package_price) && ! empty($p->package_size) && empty($p->price)) {
            $price = $package_price / $p->package_size;
        }

        if (! empty($price_without_vat) && ! empty($vat_rate)) {
            $price = $price_without_vat + (($price_without_vat * $vat_rate) / 100);
        }

        return $price;
    }

    public function select(array $request)
    {
        $columns = $this->initRead($request);
        [$name_index, $supplier_code_index] = $this->getColumnsIndex($columns, ['name', 'supplier_code']);
        $s = $this->getSupplier($request);

        $products = $errors = [];
        $all_products = $s->products;
        $all_categories = Category::all();
        $all_measures = Measure::all();
        $all_vatrates = VatRate::all();

        foreach ($this->getRecords() as $line) {
            try {
                $test = $this->findExistingProduct($line, $all_products, [
                    'supplier_code' => $supplier_code_index,
                    'name' => $name_index,
                ]);

                $price_without_vat = null;
                $vat_rate = null;
                $package_price = null;

                if ($test != null) {
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
                }

                foreach ($columns as $index => $field) {
                    $value = $line[$index];

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

                    if (! empty($value) && $field != null && $field != 'none') {
                        $p->$field = $value;
                    }
                }

                $p->price = $this->fixCalculatedPrice($p, $package_price, $price_without_vat, $vat_rate);

                $products[] = $p;
            }
            catch (\Exception $e) {
                $errors[] = implode(',', $line) . '<br/>' . $e->getMessage();
            }
        }

        return [
            'products' => $products,
            'supplier' => $s,
            'errors' => $errors,
            'sorted_fields' => $columns,
        ];
    }

    public function formatSelect($parameters)
    {
        return view('import.csvproductsselect', $parameters);
    }

    public function run(array $request)
    {
        DB::beginTransaction();

        $direct_fields = ['name', 'weight', 'description', 'price', 'supplier_code', 'package_size', 'min_quantity', 'multiple', 'portion_quantity'];

        $s = $this->getSupplier($request);
        $errors = $products = $products_ids = $new_categories = $new_measures = $new_vats = [];
        $service = app()->make('ProductsService');

        foreach ($request['import'] as $index) {
            try {
                $fields = [];

                if (isset($request['want_replace'][$index]) && $request['want_replace'][$index] != '0') {
                    $product_id = $request['want_replace'][$index];
                    $ex_novo = false;
                }
                else {
                    $product_id = null;
                    $ex_novo = true;
                }

                $fields['supplier_id'] = $s->id;
                $fields['active'] = true;

                foreach ($direct_fields as $field) {
                    $v = trim($request[$field][$index]);
                    if (filled($v)) {
                        $fields[$field] = $v;
                    }
                }

                $fields['category_id'] = $this->mapNewElements($request['category_id'][$index], $new_categories, function ($name) {
                    return Category::easyCreate(['name' => $name]);
                });

                $fields['measure_id'] = $this->mapNewElements($request['measure_id'][$index], $new_measures, function ($name) {
                    return Measure::easyCreate(['name' => $name]);
                });

                $fields['vat_rate_id'] = $this->mapNewElements($request['vat_rate_id'][$index], $new_vats, function ($name) {
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

        $reset_mode = $request['reset_list'] ?? 'no';
        if ($reset_mode == 'disable') {
            $s->products()->whereNotIn('id', $products_ids)->update(['active' => false]);
        }

        $s->import_template = json_encode(explode(',', $request['sorted_fields'] ?? ''));
        $s->save();

        DB::commit();

        return [
            'title' => __('texts.products.help.imported_notice'),
            'objects' => $products,
            'errors' => $errors,
            'extra_closing_attributes' => ['data-reload-target' => '#supplier-list'],
        ];
    }
}
