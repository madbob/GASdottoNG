<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class ProductsServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = \App\Supplier::factory()->create();
        $this->category = \App\Category::factory()->create();
        $this->measure = \App\Measure::factory()->create();

        $this->product = \App\Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id,
        ]);

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'supplier.add');
        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Creazione Prodotto con permessi sbagliati
    */
    public function test_fails_to_store()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithNoPerms);
        app()->make('ProductsService')->store([
            'supplier_id' => $this->supplier->id,
            'name' => 'Test Product',
        ]);
    }

    /*
        Creazione Prodotto
    */
    public function test_store()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $previous_quantity = $this->measure->products->count();

        $product = app()->make('ProductsService')->store([
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id,
        ]);

        $product = app()->make('ProductsService')->show($product->id);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals($this->supplier->id, $product->supplier_id);

        $measure = $this->measure->fresh();
        $this->assertEquals($previous_quantity + 1, $measure->products->count());
    }

    /*
        Creazione Prodotto con unità di misura non discreta
    */
    public function test_store_enforce_weight()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $this->measure->discrete = false;
        $this->measure->save();

        $product = app()->make('ProductsService')->store([
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id,
        ]);

        $product = app()->make('ProductsService')->show($product->id);
        $this->assertEquals(1, $product->weight);
    }

    /*
        Duplicazione Prodotto
    */
    public function test_duplicate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $product = app()->make('ProductsService')->store([
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id,
        ]);

        app()->make('VariantsService')->store([
            'product_id' => $product->id,
            'name' => 'Colore',
            'id' => ['', '', ''],
            'value' => ['Rosso', 'Verde', 'Blu'],
        ]);

        app()->make('VariantsService')->store([
            'product_id' => $product->id,
            'name' => 'Taglia',
            'id' => ['', '', ''],
            'value' => ['S', 'M', 'L'],
        ]);

        $this->nextRound();

        $duplicate = app()->make('ProductsService')->store([
            'duplicating_from' => $product->id,
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id,
        ]);

        $this->assertEquals(2, $duplicate->variants()->count());
    }

    /*
        Modifica Prodotto con permessi sbagliati
    */
    public function test_fails_to_update()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('ProductsService')->update($this->product->id, []);
    }

    /*
        Modifica Prodotto con permessi sbagliati
    */
    public function test_fails_to_update_by_admin()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithAdminPerm);
        app()->make('ProductsService')->update($this->product->id, []);
    }

    /*
        Modifica Prodotto con ID non esistente
    */
    public function test_fails_to_update_because_no_user_with_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithReferrerPerms);
        app()->make('ProductsService')->update('broken', []);
    }

    /*
        Modifica Prodotto
    */
    public function test_update()
    {
        $this->actingAs($this->userWithReferrerPerms);

        app()->make('ProductsService')->update($this->product->id, [
            'name' => 'Another Product',
            'price' => 10,
        ]);

        $product = app()->make('ProductsService')->show($this->product->id);

        $this->assertNotEquals($product->name, $this->product->name);
        $this->assertEquals($product->price, 10);
        $this->assertEquals($this->product->supplier_id, $product->supplier_id);
    }

    /*
        Accesso Prodotto con ID non esistente
    */
    public function test_fails_to_show_inexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('ProductsService')->show('random');
    }

    /*
        Accesso Prodotto
    */
    public function test_show()
    {
        $this->actingAs($this->userWithNoPerms);
        $product = app()->make('ProductsService')->show($this->product->id);

        $this->assertEquals($this->product->id, $product->id);
        $this->assertEquals($this->product->name, $product->name);
    }

    /*
        Cancellazione Prodotto con permessi sbagliati
    */
    public function test_fails_to_destroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('ProductsService')->destroy($this->product->id);
    }

    /*
        Cancellazione Prodotto
    */
    public function test_destroy()
    {
        $this->actingAs($this->userWithReferrerPerms);

        app()->make('ProductsService')->destroy($this->product->id);
        $product = app()->make('ProductsService')->show($this->product->id);
        $this->assertNotNull($product->deleted_at);
    }

    /*
        Recupero prodotto a partire dal nome
    */
    public function test_string_reading()
    {
        $string = $this->product->printableName();
        $p = productByString($string);
        $this->assertNotNull($p);
        $this->assertNull($p[1]);
        $this->assertEquals($p[0]->id, $this->product->id);
    }
}
