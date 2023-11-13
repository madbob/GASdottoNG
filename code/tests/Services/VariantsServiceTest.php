<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Exceptions\AuthException;
use App\User;
use App\Supplier;
use App\Product;
use App\Category;
use App\Measure;
use App\VariantValue;
use App\VariantCombo;

class VariantsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::factory()->create();
        $this->category = Category::factory()->create();
        $this->measure = Measure::factory()->create();

        $this->product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'category_id' => $this->category->id,
            'measure_id' => $this->measure->id
        ]);

        $this->userWithReferrerPerms = $this->createRoleAndUser($this->gas, 'supplier.modify', $this->supplier);
        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Variante con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $variant = $this->createVariant($this->product);
    }

    /*
        Salvataggio Variante con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userWithReferrerPerms);

        $variant = $this->createVariant($this->product);

        $product = $this->services['products']->show($this->product->id);
        $this->assertEquals('Colore', $variant->name);
        $this->assertEquals(3, $variant->values()->count());
        $this->assertEquals(3, $product->variant_combos->count());

        $this->nextRound();

        $this->services['variants']->store([
            'product_id' => $this->product->id,
            'name' => 'Taglia',
            'id' => ['', '', ''],
            'value' => ['S', 'M', 'L'],
        ]);

        $product = $this->services['products']->show($this->product->id);
        $this->assertEquals(9, $product->variant_combos->count());
    }

    /*
        Modifica Variante
    */
    public function testModify()
    {
        $variant = $this->createVariant($this->product);
        $old_value = $variant->values()->where('value', 'Rosso')->first();

        $variant = $this->services['variants']->store([
            'variant_id' => $variant->id,
            'name' => 'Colore',
            'id' => [
                $variant->values()->where('value', 'Rosso')->first()->id,
                $variant->values()->where('value', 'Verde')->first()->id,
                $variant->values()->where('value', 'Blu')->first()->id,
                ''
            ],
            'value' => ['Rosso', 'Verde', 'Blu', 'Giallo'],
        ]);

        $this->nextRound();
        $product = $this->services['products']->show($this->product->id);

        $this->assertEquals('Colore', $variant->name);
        $this->assertEquals(4, $variant->values()->count());
        $this->assertEquals(4, $product->variant_combos->count());

        /*
            https://github.com/madbob/GASdottoNG/issues/145
        */
        $new_value = $variant->values()->where('value', 'Rosso')->first();
        $this->assertEquals($old_value->id, $new_value->id);

        $variant = $this->services['variants']->store([
            'variant_id' => $variant->id,
            'name' => 'Colore',
            'id' => [
                $variant->values()->where('value', 'Rosso')->first()->id,
                $variant->values()->where('value', 'Verde')->first()->id,
                $variant->values()->where('value', 'Giallo')->first()->id
            ],
            'value' => ['Rosso', 'Verde', 'Giallo'],
        ]);

        $this->nextRound();
        $product = $this->services['products']->show($this->product->id);
        $this->assertEquals(3, $variant->values()->count());
        $this->assertEquals(3, $product->variant_combos->count());
        $this->assertNull(VariantValue::where('value', 'Blu')->first());
    }

    /*
        Cancellazione Variante
    */
    public function testDestroy()
    {
        $variant = $this->createVariant($this->product);
        $this->assertEquals(3, $this->product->variant_combos->count());

        $this->nextRound();

        $this->services['variants']->destroy($variant->id);
        $product = $this->services['products']->show($this->product->id);
        $this->assertEquals(0, $product->variant_combos->count());
    }

	/*
		Recupero variante a partire dal nome
	*/
	public function testStringReading()
	{
		$variant = $this->createVariant($this->product);
		$combo = $this->product->variant_combos->first();

		$string = $combo->printableName();

		$p = productByString($string);
		$this->assertNotNull($p);
		$this->assertNotNull($p[1]);
		$this->assertEquals($p[0]->id, $this->product->id);
		$this->assertEquals($p[1]->id, $combo->id);
	}

    /*
        Modifica matrice
    */
    public function testMatrixUpdate()
    {
        $variant = $this->createVariant($this->product);

        $this->nextRound();

        $ids = [];
        $actives = [];

        foreach($variant->values as $index => $val) {
            $ids[] = $val->id;

            if ($index != 0) {
                $combo = VariantCombo::byValues([$val->id]);
                $actives[] = $combo->id;
            }
        }

        $this->services['variants']->matrix($this->product, $ids, $actives, ['', 'ABC', ''], [0, 0, 1], [0.1, 0, 0]);

        $this->nextRound();

        foreach($variant->values as $index => $val) {
            $combo = VariantCombo::byValues([$val->id]);

            switch($index) {
                case 0:
                    $this->assertEquals(0, $combo->active);
                    $this->assertEquals('', $combo->code);
                    $this->assertEquals(0, $combo->price_offset);
                    $this->assertEquals(0.1, $combo->weight_offset);
                    break;

                case 1:
                    $this->assertEquals(1, $combo->active);
                    $this->assertEquals('ABC', $combo->code);
                    $this->assertEquals(0, $combo->price_offset);
                    $this->assertEquals(0, $combo->weight_offset);
                    break;

                case 2:
                    $this->assertEquals(1, $combo->active);
                    $this->assertEquals('', $combo->code);
                    $this->assertEquals(1, $combo->price_offset);
                    $this->assertEquals(0, $combo->weight_offset);
                    break;

                default:
                    throw new \Exception("Invalid combo index", 1);
            }
        }
    }

    /*
        Verifica generazione ID univoci per valori
    */
    public function testCollision()
    {
        $variant = $this->services['variants']->store([
            'product_id' => $this->product->id,
            'name' => 'Nome della variante molto lungo, perché deve generare un ID molto lungo, che viene concatenato ai valori per generare gli ID dei valori stessi',
            'id' => ['', '', ''],
            'value' => [
                'Valore molto lungo da aggiungere alla variante, per verificare come vengono tagliati i nomi. Questo è il valore numero 1',
                'Valore molto lungo da aggiungere alla variante, per verificare come vengono tagliati i nomi. Questo è il valore numero 2',
                'Valore molto lungo da aggiungere alla variante, per verificare come vengono tagliati i nomi. Questo è il valore numero 3'
            ],
        ]);

        $this->assertEquals(3, $variant->values->count());

        foreach($variant->values as $val) {
            $this->assertTrue(strlen($val->id) < 191);
        }
    }
}
