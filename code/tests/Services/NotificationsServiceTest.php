<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Exceptions\AuthException;

class NotificationsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->userNotificationAdmin = $this->createRoleAndUser($this->gas, 'gas.config,movements.admin,notifications.admin');

		$this->notification = \App\Notification::factory()->create([
            'creator_id' => $this->userNotificationAdmin->id,
        ]);

        $this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Notifica con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $this->services['notifications']->store(array());
    }

    /*
        Salvataggio Notifica con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userNotificationAdmin);

		$start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));

        $notification = $this->services['notifications']->store(array(
            'type' => 'notification',
            'content' => 'Test',
            'start_date' => printableDate($start),
			'end_date' => printableDate($end),
        ));

        $this->assertEquals('Test', $notification->content);
        $this->assertEquals($start, $notification->start_date);
		$this->assertEquals($end, $notification->end_date);
    }

    /*
        Modifica Notifica
    */
    public function testUpdate()
    {
        $this->actingAs($this->userNotificationAdmin);

		$end = date('Y-m-d', strtotime('+20 days'));

        $notification = $this->services['notifications']->update($this->notification->id, array(
            'content' => 'Test Modifica',
            'end_date' => printableDate($end),
        ));

        $this->assertEquals('Test Modifica', $notification->content);
        $this->assertEquals($end, $notification->end_date);
    }

	/*
		Selezione speciale utenti
	*/
	public function testUnroll()
	{
		$this->assertTrue($this->userNotificationAdmin->roles->isEmpty() == false);

		foreach($this->userNotificationAdmin->roles as $role) {
			$selected = unrollSpecialSelectors(['special::role::' . $role->id]);
			$this->assertEquals(count($selected), $role->users()->count());
			$this->assertTrue(in_array($this->userNotificationAdmin->id, $selected));
		}

        $this->assertTrue($this->userAdmin->roles->isEmpty() == false);

		foreach($this->userAdmin->roles as $role) {
			$selected = unrollSpecialSelectors(['special::role::' . $role->id]);
			$this->assertEquals(count($selected), $role->users()->count());
			$this->assertTrue(in_array($this->userAdmin->id, $selected));
		}
	}
}
