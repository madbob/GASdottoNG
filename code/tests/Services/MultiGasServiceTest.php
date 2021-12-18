<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App;

use App\Exceptions\AuthException;

use App\Gas;
use App\User;

class MultiGasServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->userSuperAdmin = $this->createRoleAndUser($this->gas, 'users.admin,gas.multi,supplier.view', $this->gas);
        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio nuovo GAS con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['multigas']->store(array());
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

        return $this->services['multigas']->store([
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
    public function testStore()
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
        Assegnazione fornitore a due GAS
    */
    public function testAttach()
    {
        $this->initSubgasAdminRole();
        $this->nextRound();
        $gas = $this->createGas();

        $admin = $this->createRoleAndUser($this->gas, 'supplier.add');
        $this->actingAs($admin);

        $supplier = $this->services['suppliers']->store(array(
            'name' => 'Test Supplier',
            'business_name' => 'Test Supplier SRL'
        ));

        $this->assertEquals($supplier->gas->count(), 1);

        $this->nextRound();

        $this->actingAs($this->userSuperAdmin);
        $this->userSuperAdmin = $this->services['users']->show($this->userSuperAdmin->id);

        $this->nextRound();

        $this->actingAs($this->userSuperAdmin);

        $this->services['multigas']->attach([
            'gas' => $gas->id,
            'target_id' => $supplier->id,
            'target_type' => get_class($supplier),
        ]);

        /*
            TODO: verificare effettivo accesso al fornitore da parte del secondo
            GAS, tenuto conto delle cache sparse
        */
    }
}
