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

        $type = $this->services['modifier_types']->store(array(
            'name' => 'Donazione',
            'classes' => ['App\Booking'],
        ));

        $this->assertEquals($type->exists, true);
    }

    /*
        Modifica Tipo di Movimento con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['modifier_types']->update($this->sample_type->id, array());
    }

    /*
        Modifica Tipo di Movimento
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

        $this->services['modifier_types']->update($this->sample_type->id, array(
            'name' => 'Donazioni',
        ));

        $type = $this->services['modifier_types']->show($this->sample_type->id);
        $this->assertEquals('Donazioni', $type->name);
    }

    /*
        Accesso Tipo di Movimento con ID non esistente
    */
    public function testFailsToShowInexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        $this->services['modifier_types']->show('random');
    }

    /*
        Accesso Tipo di Movimento
    */
    public function testShow()
    {
        $this->actingAs($this->userWithAdminPerm);

        $type = $this->services['modifier_types']->show($this->sample_type->id);

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
        $this->services['modifier_types']->destroy($this->sample_type->id);
    }

    /*
        Cancellazione Tipo di Movimento
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);
        $this->services['modifier_types']->destroy($this->sample_type->id);
        $this->assertNull(\App\ModifierType::find($this->sample_type->id));

        try {
            $this->services['modifier_types']->show($this->sample_type->id);
            $this->fail('should never run');
        } catch (ModelNotFoundException $e) {
            //good boy
        }
    }
}
