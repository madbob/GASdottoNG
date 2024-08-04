<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

use App\Exceptions\AuthException;
use App\User;
use App\Circle;
use App\Group;

class GroupsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Gruppo e Cerchie
    */
    public function testStore()
    {
        $this->actingAs($this->userAdmin);

        $group = app()->make('GroupsService')->store(array(
            'name' => 'Luoghi di Consegna',
        ));

        $this->nextRound();
        $group = Group::find($group->id);

        $this->assertEquals('Luoghi di Consegna', $group->name);

        $circle = app()->make('CirclesService')->store([
            'name' => 'Bar Sport',
            'description' => 'Un test',
            'group_id' => $group->id,
        ]);

        $this->nextRound();
        $circle = Circle::find($circle->id);

        $this->assertTrue($circle->is_default == true);

        $circle2 = app()->make('CirclesService')->store([
            'name' => 'Da Mario',
            'description' => 'Un altro test',
            'group_id' => $group->id,
        ]);

        $this->nextRound();
        $circle2 = Circle::find($circle2->id);

        $this->assertTrue($circle2->is_default == false);

        $users = User::all();
        foreach($users as $user) {
            $assigned = $user->circlesByGroup($group);
            $this->assertEquals(1, count($assigned->circles));
            $this->assertEquals($circle->id, $assigned->circles[0]->id);
        }
    }
}
