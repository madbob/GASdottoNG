<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use MadBob\Larastrap\Integrations\LarastrapStack;

use Tests\TestCase;
use App\Exceptions\AuthException;

class ModifierTypesServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
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
    public function test_store()
    {
        $this->actingAs($this->userWithAdminPerm);

        $request = LarastrapStack::autoreadRender('commons.addingbutton', [
            'template' => 'modifiertype.base-edit',
            'typename' => 'modtype',
            'typename_readable' => _i('Modificatore'),
            'targeturl' => 'modtypes',
            'autoread' => true,
        ]);

        $request = array_merge($request, [
            'name' => 'Donazione',
            'classes' => ['App\Booking'],
        ]);

        $type = app()->make('ModifierTypesService')->store($request);
        $this->assertTrue($type->exists);

        $found = false;
        $types = \App\ModifierType::byClass(\App\Booking::class);
        foreach ($types as $t) {
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
    public function test_fails_to_update()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        $request = LarastrapStack::autoreadRender('modifiertype.edit', ['modtype' => $this->sample_type]);
        $request = array_merge($request, [
            'name' => 'Donazioni',
        ]);

        app()->make('ModifierTypesService')->store($request);
    }

    /*
        Modifica Tipo di Movimento
    */
    public function test_update()
    {
        $this->actingAs($this->userWithAdminPerm);

        $request = LarastrapStack::autoreadRender('modifiertype.edit', ['modtype' => $this->sample_type]);
        $request = array_merge($request, [
            'name' => 'Donazioni',
        ]);

        app()->make('ModifierTypesService')->store($request);

        $type = app()->make('ModifierTypesService')->show($this->sample_type->id);
        $this->assertEquals('Donazioni', $type->name);
    }

    /*
        Accesso Tipo di Movimento con ID non esistente
    */
    public function test_fails_to_show_inexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        app()->make('ModifierTypesService')->show('random');
    }

    /*
        Accesso Tipo di Movimento
    */
    public function test_show()
    {
        $this->actingAs($this->userWithAdminPerm);

        $type = app()->make('ModifierTypesService')->show($this->sample_type->id);

        $this->assertEquals($this->sample_type->id, $type->id);
        $this->assertEquals($this->sample_type->name, $type->name);
    }

    /*
        Cancellazione Tipo di Movimento con permessi sbagliati
    */
    public function test_fails_to_destroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('ModifierTypesService')->destroy($this->sample_type->id);
    }

    /*
        Cancellazione Tipo di Movimento
    */
    public function test_destroy()
    {
        $this->actingAs($this->userWithAdminPerm);
        app()->make('ModifierTypesService')->destroy($this->sample_type->id);
        $this->assertNull(\App\ModifierType::find($this->sample_type->id));

        try {
            app()->make('ModifierTypesService')->show($this->sample_type->id);
            $this->fail('should never run');
        }
        catch (ModelNotFoundException $e) {
            //good boy
        }
    }
}
