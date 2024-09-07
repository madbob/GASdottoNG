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

    private function createGroupWithCircle()
    {
        $this->actingAs($this->userAdmin);

        $group = app()->make('GroupsService')->store(array(
            'name' => 'Luoghi di Consegna',
        ));

        $this->nextRound();
        $group = Group::find($group->id);

        $circle = app()->make('CirclesService')->store([
            'name' => 'Bar Sport',
            'description' => 'Un test',
            'group_id' => $group->id,
        ]);

        $this->nextRound();
        $circle = Circle::find($circle->id);

        $circle2 = app()->make('CirclesService')->store([
            'name' => 'Da Mario',
            'description' => 'Un altro test',
            'group_id' => $group->id,
        ]);

        $this->nextRound();
        $circle2 = Circle::find($circle2->id);

        return [$group, $circle, $circle2];
    }

    /*
        Salvataggio Gruppo e Cerchie
    */
    public function testStore()
    {
        list($group, $circle, $circle2) = $this->createGroupWithCircle();

        $this->nextRound();

        $this->assertEquals('Luoghi di Consegna', $group->name);
        $this->assertTrue($circle->is_default == true);
        $this->assertTrue($circle2->is_default == false);

        $group = Group::find($group->id);
        $this->assertEquals(2, $group->circles()->count());

        $users = User::all();
        $this->assertTrue($users->count() > 0);

        foreach($users as $user) {
            $assigned = $user->circlesByGroup($group);
            $this->assertEquals(1, count($assigned->circles));
            $this->assertEquals($circle->id, $assigned->circles[0]->id);
        }
    }

    /*
        Aggiornamento Gruppo e Cerchie
    */
    public function testUpdate()
    {
        list($group, $circle, $circle2) = $this->createGroupWithCircle();

        $this->nextRound();

        app()->make('CirclesService')->update($circle->id, [
            'name' => 'Bar Sport 2',
            'description' => 'Un altro test',
            'is_default' => true,
        ]);

        $this->nextRound();
        $circle = Circle::find($circle->id);
        $this->assertEquals('Bar Sport 2', $circle->name);

        $this->nextRound();
        $this->assertTrue($circle->is_default == true);
        app()->make('CirclesService')->destroy($circle->id);
        $this->nextRound();

        $group = Group::find($group->id);
        $this->assertEquals(1, $group->circles()->count());

        $circle2 = Circle::find($circle2->id);
        $this->assertTrue($circle2->is_default == true);

        $users = User::all();
        foreach($users as $user) {
            $assigned = $user->circlesByGroup($group);
            $this->assertEquals(1, count($assigned->circles));
            $this->assertEquals($circle2->id, $assigned->circles[0]->id);
        }
    }
}
