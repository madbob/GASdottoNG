<?php

namespace Tests;

use App\Importers\GDXP\Suppliers;

class ImportersTest extends TestCase
{
    public function testGdxp()
    {
        $data = [];
        $path = base_path('tests/data/gdxp.json');
        $info = json_decode(file_get_contents($path));

        foreach($info->blocks as $c) {
            $data[] = Suppliers::importJSON($info, $c->supplier, null);
        }

        $this->assertEquals(1, count($data));
        $this->assertEquals(\App\Supplier::class, get_class($data[0]));
        $supplier = $data[0];
        $this->assertEquals('Teanatura', $supplier->name);
        $this->assertEquals(5, $supplier->products->count());
        $this->assertEquals(2, $supplier->contacts->count());

        foreach ($info->blocks[0]->supplier->products as $real_product) {
            $p = $supplier->products()->where('name', $real_product->name)->first();
            $this->assertNotNull($p);
            $this->assertEquals($p->measure->name, $real_product->um);
            $this->assertEquals($p->category->name, $real_product->category);
            $this->assertEquals($p->description, $real_product->description);
        }
    }

    /*
        https://github.com/madbob/GASdottoNG/issues/143
    */
    public function testProductsCsv()
    {
        $data = [];
        $path = base_path('tests/data/products.csv');

        $importer = \App\Importers\CSV\CSVImporter::getImporter('products');
        $supplier = \App\Supplier::factory()->create();

        $request = new \Illuminate\Http\Request();
        $request->merge([
            'path' => $path,
            'supplier_id' => $supplier->id,
            'column' => ['name', 'supplier_code', 'measure', 'category', 'price', 'vat', 'package_price', 'package_size', 'weight', 'multiple', 'min_quantity'],
        ]);

        $data = $importer->select($request);

        $this->assertEquals(10, count($data['products']));
        $this->assertEquals(0, count($data['errors']));
        $this->assertEquals($supplier->id, $data['supplier']->id);

        $this->assertEquals(3.3, $data['products'][0]->price);
        $this->assertEquals(0.1, $data['products'][0]->weight);
        $this->assertEquals(0, $data['products'][0]->min_quantity);
        $this->assertEquals('Biscotti e dolci', $data['products'][0]->temp_category_name);
        $this->assertEquals('Barattoli', $data['products'][0]->temp_measure_name);

        $this->assertEquals(4.8, $data['products'][8]->price);
        $this->assertEquals(10, $data['products'][8]->package_size);
        $this->assertEquals(5, $data['products'][8]->multiple);
        $this->assertEquals(0, $data['products'][8]->min_quantity);
        $this->assertEquals('4', $data['products'][8]->temp_vat_rate_name);

        $this->assertEquals('Mandorle Bio sgusciate 600gr', $data['products'][9]->name);
        $this->assertEquals(1, $data['products'][9]->multiple);
        $this->assertEquals(5, $data['products'][9]->min_quantity);
        $this->assertEquals('Frutta secca', $data['products'][9]->temp_category_name);
        $this->assertEquals('Sacchetti', $data['products'][9]->temp_measure_name);
        $this->assertEquals(0, $data['products'][9]->vat_rate_id);
    }
}
