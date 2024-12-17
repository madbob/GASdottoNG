<?php

namespace Tests;

use Illuminate\Support\Facades\Notification;

use App\Importers\GDXP\Suppliers;
use App\Notifications\ManualWelcomeMessage;
use App\User;
use App\Supplier;
use App\Category;
use App\Measure;

class ImportersTest extends TestCase
{
    /*
        Importazione GDXP 1.0
    */
    public function test_gdxp()
    {
        $data = [];
        $path = base_path('tests/data/gdxp.json');
        $info = json_decode(file_get_contents($path));

        foreach ($info->blocks as $c) {
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
        Importazione GDXP 2.0
    */
    public function test_gdxp2()
    {
        $data = [];
        $path = base_path('tests/data/gdxp2.json');
        $info = json_decode(file_get_contents($path));

        foreach ($info->blocks as $c) {
            $data[] = Suppliers::importJSON($info, $c->supplier, null);
        }

        $this->assertEquals(1, count($data));
        $this->assertEquals(Supplier::class, get_class($data[0]));

        $supplier = $data[0];
        $this->assertEquals('Officina Naturae', $supplier->name);
        $this->assertEquals(3, $supplier->products->count());
        $this->assertEquals(3, $supplier->contacts->count());

        $this->assertEquals(2, $supplier->modifiers->count());
        $has_shipping = $has_discount = false;

        foreach ($supplier->modifiers as $mod) {
            $definitions = $mod->definitions;

            if ($mod->modifierType->identifier == 'shipping') {
                $has_shipping = true;
                $this->assertEquals('absolute', $mod->value);
                $this->assertEquals(2, count($definitions));
                $this->assertEquals(450, $definitions[0]->threshold);
                $this->assertEquals(0, $definitions[0]->amount);
                $this->assertEquals(0, $definitions[1]->threshold);
                $this->assertEquals(10, $definitions[1]->amount);
                $this->assertEquals('price', $mod->applies_type);
            }
            elseif ($mod->modifierType->identifier == 'discount') {
                $has_discount = true;
                $this->assertEquals('percentage', $mod->value);
                $this->assertEquals(1, count($definitions));
                $this->assertEquals(1000, $definitions[0]->threshold);
                $this->assertEquals(3, $definitions[0]->amount);
                $this->assertEquals('price', $mod->applies_type);
            }
        }

        $this->assertTrue($has_discount);
        $this->assertTrue($has_shipping);
    }

    /*
        https://github.com/madbob/GASdottoNG/issues/143
    */
    public function test_products_csv()
    {
        $data = [];
        $path = base_path('tests/data/products.csv');

        $importer = \App\Importers\CSV\CSVImporter::getImporter('products');
        $supplier = \App\Supplier::factory()->create();

        $file = fopen($path, 'r');
        $first_row = fgetcsv($file);
        $reference_name = $first_row[0];

        $category = \App\Category::factory()->create();
        $measure = \App\Measure::factory()->create();

        $reference = \App\Product::factory()->create([
            'name' => $reference_name,
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'measure_id' => $measure->id,
        ]);

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
        $this->assertEquals($reference->id, $data['products'][0]->want_replace->id);

        $this->assertEquals(4.8, $data['products'][8]->price);
        $this->assertEquals(10, $data['products'][8]->package_size);
        $this->assertEquals(5, $data['products'][8]->multiple);
        $this->assertEquals(0, $data['products'][8]->min_quantity);
        $this->assertEquals('4', $data['products'][8]->temp_vat_rate_name);
        $this->assertNull($data['products'][8]->want_replace);

        $this->assertEquals('Mandorle Bio sgusciate 600gr', $data['products'][9]->name);
        $this->assertEquals(1, $data['products'][9]->multiple);
        $this->assertEquals(5, $data['products'][9]->min_quantity);
        $this->assertEquals('Frutta secca', $data['products'][9]->temp_category_name);
        $this->assertEquals('Sacchetti', $data['products'][9]->temp_measure_name);
        $this->assertEquals(0, $data['products'][9]->vat_rate_id);

        $final_block = [
            'supplier_id' => $supplier->id,
            'reset_list' => 'disable',
            'import' => [],
            'weight' => [],
            'package_size' => [],
            'min_quantity' => [],
            'multiple' => [],
            'portion_quantity' => [],
            'name' => [],
            'description' => [],
            'price' => [],
            'category_id' => [],
            'measure_id' => [],
            'vat_rate_id' => [],
            'supplier_code' => [],
            'want_replace' => [],
        ];

        foreach ($data['products'] as $index => $prod) {
            $final_block['import'][] = $index;
            $final_block['weight'][] = $prod->weight;
            $final_block['package_size'][] = $prod->package_size;
            $final_block['min_quantity'][] = $prod->min_quantity;
            $final_block['multiple'][] = $prod->multiple;
            $final_block['portion_quantity'][] = $prod->portion_quantity;
            $final_block['name'][] = $prod->name;
            $final_block['description'][] = $prod->description;
            $final_block['price'][] = $prod->price;
            $final_block['category_id'][] = $prod->temp_category_name ? sprintf('new:%s', $prod->temp_category_name) : $prod->category_id;
            $final_block['measure_id'][] = $prod->temp_measure_name ? sprintf('new:%s', $prod->temp_measure_name) : $prod->measure_id;
            $final_block['vat_rate_id'][] = $prod->vat_rate_id;
            $final_block['supplier_code'][] = $prod->supplier_code;
            $final_block['want_replace'][] = $prod->want_replace ? $prod->want_replace->id : 0;
        }

        $request = new \Illuminate\Http\Request();
        $request->merge($final_block);

        $data = $importer->run($request);

        $this->nextRound();

        $supplier = $supplier->fresh();
        $this->assertEquals(10, $supplier->products()->count());

        $this->assertNotNull(Category::where('name', 'Biscotti e dolci')->first());
        $this->assertNotNull(Category::where('name', 'Frutta secca')->first());
        $this->assertNotNull(Measure::where('name', 'Barattoli')->first());
        $this->assertNotNull(Measure::where('name', 'Sacchetti')->first());
    }

    public function test_users_csv()
    {
        $this->actingAs($this->userAdmin);

        Notification::fake();

        $data = [];
        $path = base_path('tests/data/users.csv');

        $importer = \App\Importers\CSV\CSVImporter::getImporter('users');

        $request = new \Illuminate\Http\Request();
        $request->merge([
            'path' => $path,
            'column' => ['firstname', 'lastname', 'username', 'password', 'email', 'phone'],
        ]);

        $response = $importer->run($request);
        $this->assertEquals(3, count($response['objects']));

        $user1 = User::where('username', 'mario')->first();
        $this->assertNotNull($user1);
        $this->assertEquals('mario@example.com', $user1->email);
        $this->assertEquals('', $user1->mobile);

        $user2 = User::where('username', 'giovanni')->first();
        $this->assertNotNull($user2);
        $contacts = $user2->getContactsByType(['phone', 'mobile']);
        $this->assertEquals(1, count($contacts));

        Notification::assertSentTo([$user1, $user2], ManualWelcomeMessage::class);
        Notification::assertCount(2);
    }

    public function test_movements_csv()
    {
        $this->actingAs($this->userAdmin);

        $data = [];
        $path = base_path('tests/data/movements.csv');

        User::factory()->create([
            'gas_id' => $this->gas->id,
            'username' => 'mario',
        ]);

        User::factory()->create([
            'gas_id' => $this->gas->id,
            'username' => 'luigi',
        ]);

        Supplier::factory()->create([
            'name' => 'Fornitore',
        ]);

        Supplier::factory()->create([
            'name' => 'Fornitore 2',
            'vat' => '01234567',
        ]);

        $importer = \App\Importers\CSV\CSVImporter::getImporter('movements');

        $request = new \Illuminate\Http\Request();
        $request->merge([
            'path' => $path,
            'column' => ['date', 'amount', 'user', 'supplier'],
        ]);

        $data = $importer->select($request);

        $this->assertEquals(4, count($data['movements']));
        $this->assertEquals(0, count($data['errors']));
    }
}
