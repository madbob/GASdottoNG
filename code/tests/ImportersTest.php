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
}
