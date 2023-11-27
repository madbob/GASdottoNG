<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Exceptions\AuthException;
use App\Notifications\GenericNotificationWrapper;

class NotificationsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->userNotificationAdmin = $this->createRoleAndUser($this->gas, 'gas.config,movements.admin,notifications.admin');
		$this->userWithNoPerms = \App\User::factory()->create(['gas_id' => $this->gas->id]);

		$this->notification = \App\Notification::factory()->create([
            'creator_id' => $this->userNotificationAdmin->id,
        ]);
    }

    /*
        Salvataggio Notifica con permessi sbagliati
    */
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('NotificationsService')->store(array());
    }

    /*
        Salvataggio Notifica con permessi corretti
    */
    public function testStore()
    {
        $this->actingAs($this->userNotificationAdmin);

		$start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));

        $notification = app()->make('NotificationsService')->store(array(
            'type' => 'notification',
            'content' => 'Test',
            'start_date' => printableDate($start),
			'end_date' => printableDate($end),
        ));

        $this->assertEquals('Test', $notification->content);
        $this->assertEquals($this->userNotificationAdmin->id, $notification->creator->id);
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

        $notification = app()->make('NotificationsService')->update($this->notification->id, array(
            'content' => 'Test Modifica',
            'end_date' => printableDate($end),
        ));

        $this->assertEquals('Test Modifica', $notification->content);
        $this->assertEquals($end, $notification->end_date);
    }

	/*
		Lettura Notifica
	*/
	public function testRead()
	{
		$this->actingAs($this->userNotificationAdmin);

		$start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));

		$notification = app()->make('NotificationsService')->store([
			'users' => [$this->userWithNoPerms->id],
            'type' => 'notification',
            'content' => 'Altro Test',
            'start_date' => printableDate($start),
			'end_date' => printableDate($end),
        ]);

		$initial_count = $this->userWithNoPerms->notifications()->count();
		$this->assertTrue($initial_count > 0);
		$this->assertTrue($notification->hasUser($this->userWithNoPerms));
        $this->assertFalse($notification->hasUser($this->userNotificationAdmin));
        $this->assertEquals($this->userWithNoPerms->printableName(), $notification->printableName());

		$this->actingAs($this->userWithNoPerms);
		app()->make('NotificationsService')->markread($notification->id);

		$this->nextRound();

		$next_count = $this->userWithNoPerms->notifications()->count();
		$this->assertTrue($initial_count > $next_count);
	}

    /*
        Invio mail
    */
    public function testMail()
	{
        Notification::fake();

		$this->actingAs($this->userNotificationAdmin);

		$start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));

		$notification = app()->make('NotificationsService')->store([
			'users' => [$this->userWithNoPerms->id, $this->userNotificationAdmin->id],
            'type' => 'notification',
            'mailed' => true,
            'content' => 'Altro Test',
            'start_date' => printableDate($start),
			'end_date' => printableDate($end),
        ]);

        Notification::assertSentTo([$this->userWithNoPerms, $this->userNotificationAdmin], GenericNotificationWrapper::class);
        Notification::assertCount(2);

        $this->assertEquals('2 utenti', $notification->printableName());
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

    /*
        Cancellazione Fornitore
    */
    public function testDestroy()
    {
        $this->actingAs($this->userNotificationAdmin);

        $list = app()->make('NotificationsService')->list(null, null);
        app()->make('NotificationsService')->destroy($this->notification->id);

        $this->nextRound();

        $list_next = app()->make('NotificationsService')->list(null, null);
        $this->assertEquals($list_next->count(), $list->count() - 1);
    }
}
