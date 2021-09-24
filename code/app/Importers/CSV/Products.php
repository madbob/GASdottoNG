<?php

namespace App\Importers\CSV;

use Illuminate\Support\Str;

use App;
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
                'mandatory' => true
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
                'label' => _i('Peso'),
            ],
            'min_quantity' => (object) [
                'label' => _i('Ordine Minimo'),
            ],
            'multiple' => (object) [
                'label' => _i('Ordinabile per Multipli')
            ],
        ];
    }

    public function testAccess($request)
    {
        $supplier_id = $request->input('supplier_id');
        $s = Supplier::findOrFail($supplier_id);
        return $request->user()->can('supplier.modify', $s);
    }

    public function guess($request)
    {
        $supplier_id = $request->input('supplier_id');
        $s = Supplier::findOrFail($supplier_id);

        return $this->storeUploadedFile($request, [
            'type' => 'products',
            'next_step' => 'select',
            'extra_fields' => [
                'supplier_id' => $s->id
            ],
            'extra_description' => [
                _i('Le categorie e le unità di misura il cui nome non sarà trovato tra quelle esistenti saranno create.')
            ],
            'sorting_fields' => $this->fields(),
        ]);
    }

    public function select($request)
    {
        list($reader, $columns) = $this->initRead($request);
        list($name_index, $supplier_code_index) = $this->getColumnsIndex($columns, ['name', 'supplier_code']);
        $target_separator = ',';

        $supplier_id = $request->input('supplier_id');
        $s = Supplier::findOrFail($supplier_id);

        $products = [];
        $errors = [];

        foreach($reader->getRecords() as $line) {
            if (empty($line) || (count($line) == 1 && empty($line[0]))) {
                continue;
            }

            try {
                $name = $line[$name_index];

                $p = new Product();
                $p->name = $name;
                $p->category_id = 'non-specificato';
                $p->measure_id = 'non-specificato';
                $p->min_quantity = 0;
                $p->multiple = 0;
                $p->package_size = 0;

                if ($supplier_code_index == -1 && !empty($line[$supplier_code_index])) {
                    $test = $s->products()->where('name', $name)->orderBy('id', 'desc')->first();
                }
                else {
                    $test = $s->products()->where('name', $name)->orWhere('supplier_code', $line[$supplier_code_index])->orderBy('id', 'desc')->first();
                }

                if (is_null($test) == false) {
                    $p->want_replace = $test->id;
                }
                else {
                    $p->want_replace = 0;
                }

                $price_without_vat = null;
                $vat_rate = null;
                $package_price = null;

                foreach ($columns as $index => $field) {
                    $value = trim($line[$index]);

                    if ($field == 'none') {
                        continue;
                    }
                    elseif ($field == 'category') {
                        $test_category = Category::where('name', $value)->first();
                        if (is_null($test_category)) {
                            $field = 'temp_category_name';
                        }
                        else {
                            $p->category_id = $test_category->id;
                        }
                    }
                    elseif ($field == 'measure') {
                        $test_measure = Measure::where('name', $value)->first();
                        if (is_null($test_measure)) {
                            $field = 'temp_measure_name';
                        }
                        else {
                            $p->measure_id = $test_measure->id;
                        }
                    }
                    elseif ($field == 'price_without_vat') {
                        $price_without_vat = str_replace(',', '.', $value);
                        continue;
                    }
                    elseif ($field == 'vat') {
                        $value = str_replace(',', '.', $value);
                        $vat_rate = $value = (float) $value;

                        $test_vat = VatRate::where('percentage', $value)->first();
                        if (is_null($test_vat)) {
                            $field = 'temp_vat_rate_name';
                        }
                        else {
                            $p->vat_rate_id = $test_vat->id;
                        }
                    }
                    elseif ($field == 'package_price') {
                        $package_price = str_replace(',', '.', $value);
                        continue;
                    }

                    if (!empty($value)) {
                        $p->$field = $value;
                    }
                }

                if (!empty($package_price) && !empty($p->package_size) && empty($p->price)) {
                    $p->price = $package_price / $p->package_size;
                }

                if (!empty($price_without_vat) && !empty($vat_rate)) {
                    $p->price = $price_without_vat + (($price_without_vat * $vat_rate) / 100);
                }

                $products[] = $p;
            }
            catch (\Exception $e) {
                $errors[] = join($target_separator, $line).'<br/>'.$e->getMessage();
            }
        }

        return ['products' => $products, 'supplier' => $s, 'errors' => $errors];
    }

    public function formatSelect($parameters)
    {
        return view('import.csvproductsselect', $parameters);
    }

    private function mapNewElements($value, &$cached, $createNew)
    {
        if (Str::startsWith($value, 'new:')) {
            $name = Str::after($value, 'new:');
            if (!empty($name)) {
                if (!isset($cached[$name])) {
                    $obj = $createNew($name);
                    $obj->save();
                    $cached[$name] = $obj->id;
                }

                return $cached[$name];
            }
            else {
                return $name;
            }
        }

        return $value;
    }

    public function run($request)
    {
        DB::beginTransaction();

        $imports = $request->input('import');
        $names = $request->input('name');
        $descriptions = $request->input('description');
        $prices = $request->input('price');
        $categories = $request->input('category_id');
        $measures = $request->input('measure_id');
        $vat_rates = $request->input('vat_rate_id');
        $codes = $request->input('supplier_code');
        $sizes = $request->input('package_size');
        $mins = $request->input('min_quantity');
        $multiples = $request->input('multiple');
        $replaces = $request->input('want_replace');

        $supplier_id = $request->input('supplier_id');
        $s = Supplier::findOrFail($supplier_id);

        $errors = [];
        $products = [];
        $products_ids = [];
        $new_categories = [];
        $new_measures = [];
        $new_vats = [];

        foreach($imports as $index) {
            try {
                if ($replaces[$index] != '0') {
                    $p = Product::find($replaces[$index]);
                }
                else {
                    $p = new Product();
                    $p->supplier_id = $s->id;
                }

                $p->active = true;

                $p->name = $names[$index];
                $p->description = $descriptions[$index];
                $p->price = $prices[$index];
                $p->supplier_code = $codes[$index];
                $p->package_size = $sizes[$index];
                $p->min_quantity = $mins[$index];
                $p->multiple = $multiples[$index];

                $p->category_id = $this->mapNewElements($categories[$index], $new_categories, function($name) {
                    $category = new Category();
                    $category->name = $name;
                    $category->save();
                    return $category;
                });

                $p->measure_id = $this->mapNewElements($measures[$index], $new_measures, function($name) {
                    $measure = new Measure();
                    $measure->name = $name;
                    $measure->save();
                    return $measure;
                });

                $p->vat_rate_id = $this->mapNewElements($vat_rates[$index], $new_vats, function($name) {
                    $name = (float) $name;
                    $vat = new VatRate();
                    $vat->percentage = $name;
                    $vat->name = sprintf('%f %%', round($name, 2));
                    $vat->save();
                    return $vat;
                });

                $p->save();
                $products[] = $p;
                $products_ids[] = $p->id;
            }
            catch (\Exception $e) {
                $errors[] = $index . '<br/>' . $e->getMessage();
            }
        }

        if ($request->has('reset_list')) {
            $s->products()->whereNotIn('id', $products_ids)->update(['active' => false]);
        }

        DB::commit();

        return [
            'title' => _i('Prodotti importati'),
            'objects' => $products,
            'errors' => $errors,
            'extra_closing_attributes' => [
                'data-reload-target' => '#supplier-list'
            ]
        ];
    }
}
