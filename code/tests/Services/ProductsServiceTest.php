<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class ProductsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier = \App\Supplier::factory()->create();
        $this->category = \App\Category::factory()->create();
        $this->measure = \App\Measure::factory()->create();

        $this->product = \App\Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'supplier.add');
        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Creazione Prodotto con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithNoPerms);
        $this->services['products']->store(array(
            'supplier_id' => $this->supplier->id,
            'name' => 'Test Product'
        ));
    }

    /*
        Creazione Prodotto
    */
    public function testStore()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $previous_quantity = $this->measure->products->count();

        $product = $this->services['products']->store([
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $product = $this->services['products']->show($product->id);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals($this->supplier->id, $product->supplier_id);

        $measure = $this->measure->fresh();
        $this->assertEquals($previous_quantity + 1, $measure->products->count());
    }

    /*
        Creazione Prodotto con unità di misura non discreta
    */
    public function testStoreEnforceWeight()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $this->measure->discrete = false;
        $this->measure->save();

        $product = $this->services['products']->store([
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $product = $this->services['products']->show($product->id);
        $this->assertEquals(1, $product->weight);
    }

    /*
        Duplicazione Prodotto
    */
    public function testDuplicate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $product = $this->services['products']->store(array(
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ));

        $this->services['variants']->store([
            'product_id' => $product->id,
            'name' => 'Colore',
            'id' => ['', '', ''],
            'value' => ['Rosso', 'Verde', 'Blu'],
        ]);

        $this->services['variants']->store([
            'product_id' => $product->id,
            'name' => 'Taglia',
            'id' => ['', '', ''],
            'value' => ['S', 'M', 'L'],
        ]);

        $this->nextRound();

        $duplicate = $this->services['products']->store(array(
            'duplicating_from' => $product->id,
            'name' => 'Test Product',
            'price' => rand(),
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ));

        $this->assertEquals(2, $duplicate->variants()->count());
    }

    /*
        Modifica Prodotto con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['products']->update($this->product->id, array());
    }

    /*
        Modifica Prodotto con permessi sbagliati
    */
    public function testFailsToUpdateByAdmin()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['products']->update($this->product->id, array());
    }

    /*
        Modifica Prodotto con ID non esistente
    */
    public function testFailsToUpdateBecauseNoUserWithID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithReferrerPerms);
        $this->services['products']->update('broken', array());
    }

    /*
        Modifica Prodotto
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $this->services['products']->update($this->product->id, array(
            'name' => 'Another Product',
            'price' => 10,
        ));

        $product = $this->services['products']->show($this->product->id);

        $this->assertNotEquals($product->name, $this->product->name);
        $this->assertEquals($product->price, 10);
        $this->assertEquals($this->product->supplier_id, $product->supplier_id);
    }

    /*
        Accesso Prodotto con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['products']->show('random');
    }

    /*
        Accesso Prodotto
    */
    public function testShow()
    {
        $this->actingAs($this->userWithNoPerms);
        $product = $this->services['products']->show($this->product->id);

        $this->assertEquals($this->product->id, $product->id);
        $this->assertEquals($this->product->name, $product->name);
    }

    /*
        Cancellazione Prodotto con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['products']->destroy($this->product->id);
    }

    /*
        Cancellazione Prodotto
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $this->services['products']->destroy($this->product->id);
        $product = $this->services['products']->show($this->product->id);
        $this->assertNotNull($product->deleted_at);
    }

	/*
		Recupero prodotto a partire dal nome
	*/
	public function testStringReading()
	{
		$string = $this->product->printableName();
		$p = productByString($string);
		$this->assertNotNull($p);
		$this->assertNull($p[1]);
		$this->assertEquals($p[0]->id, $this->product->id);
	}
}
