<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Artisan;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class ModifierTypesServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'gas.config');
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

        $this->sample_type = \App\ModifierType::factory()->create([
			'classes' => ['App\Booking', 'App\Product'],
        ]);
    }

    /*
        Creazione Tipo di Movimento
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $type = app()->make('ModifierTypesService')->store([
            'name' => 'Donazione',
            'classes' => ['App\Booking'],
        ]);

        $this->assertTrue($type->exists);

        $found = false;
        $types = \App\ModifierType::byClass(\App\Booking::class);
        foreach($types as $t) {
            if ($t->id == $type->id) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    /*
        Modifica Tipo di Movimento con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('ModifierTypesService')->update($this->sample_type->id, array());
    }

    /*
        Modifica Tipo di Movimento
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

        app()->make('ModifierTypesService')->update($this->sample_type->id, array(
            'name' => 'Donazioni',
        ));

        $type = app()->make('ModifierTypesService')->show($this->sample_type->id);
        $this->assertEquals('Donazioni', $type->name);
    }

    /*
        Accesso Tipo di Movimento con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        app()->make('ModifierTypesService')->show('random');
    }

    /*
        Accesso Tipo di Movimento
    */
    public function testShow()
    {
        $this->actingAs($this->userWithAdminPerm);

        $type = app()->make('ModifierTypesService')->show($this->sample_type->id);

        $this->assertEquals($this->sample_type->id, $type->id);
        $this->assertEquals($this->sample_type->name, $type->name);
    }

    /*
        Cancellazione Tipo di Movimento con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('ModifierTypesService')->destroy($this->sample_type->id);
    }

    /*
        Cancellazione Tipo di Movimento
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);
        app()->make('ModifierTypesService')->destroy($this->sample_type->id);
        $this->assertNull(\App\ModifierType::find($this->sample_type->id));

        try {
            app()->make('ModifierTypesService')->show($this->sample_type->id);
            $this->fail('should never run');
        } catch (ModelNotFoundException $e) {
            //good boy
        }
    }
}
