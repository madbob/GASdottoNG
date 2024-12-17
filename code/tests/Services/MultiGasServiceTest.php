<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;


use App\Exceptions\AuthException;

use App\Gas;
use App\User;

class MultiGasServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        /*
            Solo quando la configurazione "multigas" è abilitata, vengono
            effettivamente caricate le regole di controllo permessi relative.
            Pertanto qui, dopo aver impostato tale configurazione, le regole
            vengono ricaricate; altrimenti, tutte le funzioni successive
            ritornano un "not authorized" non essendo in grado di riconoscere il
            permesso "gas.multi"
        */
        $this->gas->setConfig('multigas', '1');
        app()->make('RolesService')->registerPolicies();

        $this->userSuperAdmin = $this->createRoleAndUser($this->gas, 'users.admin,gas.multi,supplier.view', $this->gas);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio nuovo GAS con permessi sbagliati
    */
    public function test_fails_to_store()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('MultiGasService')->store([]);
    }

    private function initSubgasAdminRole()
    {
        $role = \App\Role::factory()->create([
            'actions' => 'gas.access,gas.config,supplier.view,supplier.book,supplier.add,users.admin,users.movements,movements.admin,notifications.admin',
        ]);

        $this->userSuperAdmin->gas->setConfig('roles', [
            'user' => -1,
            'friend' => -1,
            'multigas' => $role->id,
        ]);

        return $role;
    }

    private function createGas()
    {
        $this->actingAs($this->userSuperAdmin);

        return app()->make('MultiGasService')->store([
            'name' => 'Test GAS',
            'username' => 'testuser',
            'firstname' => 'Test',
            'lastname' => 'User',
            'password' => 'test',
        ]);
    }

    /*
        Salvataggio nuovo GAS con permessi corretti
    */
    public function test_store()
    {
        $role = $this->initSubgasAdminRole();
        $this->nextRound();
        $gas = $this->createGas();

        $this->nextRound();

        $this->assertEquals(Gas::count(), 2);
        $user = User::withoutGlobalScopes()->where('username', 'testuser')->first();
        $this->assertNotNull($user);
        $this->assertEquals($user->gas_id, $gas->id);

        $this->assertEquals($user->roles()->where('roles.id', $role->id)->count(), 1);
    }

    /*
        Aggiornamento GAS
    */
    public function test_update()
    {
        $role = $this->initSubgasAdminRole();
        $this->nextRound();
        $gas = $this->createGas();

        $this->nextRound();
        $this->userSuperAdmin = app()->make('UsersService')->show($this->userSuperAdmin->id);

        $this->nextRound();
        $this->actingAs($this->userSuperAdmin);
        $gas = app()->make('MultiGasService')->update($gas->id, ['name' => 'Cambio nome']);

        $this->nextRound();

        $list = app()->make('MultiGasService')->list();
        $this->assertCount(2, $list);

        $found = 0;
        foreach ($list as $g) {
            if ($g->name == 'Cambio nome') {
                $found++;
            }
        }

        $this->assertEquals($found, 1);
    }

    /*
        Assegnazione fornitore a due GAS
    */
    public function test_attach()
    {
        $this->initSubgasAdminRole();
        $this->nextRound();
        $gas = $this->createGas();

        $admin = $this->createRoleAndUser($this->gas, 'supplier.add');
        $this->actingAs($admin);

        $supplier = app()->make('SuppliersService')->store([
            'name' => 'Test Supplier',
            'business_name' => 'Test Supplier SRL',
        ]);

        $this->assertEquals($supplier->gas->count(), 1);

        $this->nextRound();

        $this->actingAs($this->userSuperAdmin);
        $this->userSuperAdmin = app()->make('UsersService')->show($this->userSuperAdmin->id);

        $this->nextRound();

        $this->actingAs($this->userSuperAdmin);

        app()->make('MultiGasService')->attach([
            'gas' => $gas->id,
            'target_id' => $supplier->id,
            'target_type' => get_class($supplier),
        ]);

        /*
            Questo è necessario per resettare lo stato interno dell'hub e fargli
            rileggere i GAS attualmente sul DB
        */
        app()->forgetInstance('GlobalScopeHub');

        $this->nextRound();

        $suppliers = app()->make('SuppliersService')->list();
        $this->assertCount(1, $suppliers);
    }
}
