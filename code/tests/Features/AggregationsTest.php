<?php

namespace Tests\Features;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use MadBob\Larastrap\Integrations\LarastrapStack;

use App\ModifierType;

class AggregationsTest extends TestCase
{
    use DatabaseTransactions;

    private function createBasicGroup($params)
    {
        $this->actingAs($this->userAdmin);

        $groups_service = app()->make('GroupsService');
        $circles_service = app()->make('CirclesService');

        $group = $groups_service->store(['name' => 'Gruppo Test']);
        $groups_service->update($group->id, array_merge(['name' => 'Gruppo Test'], $params));

        $this->nextRound();

        $circles_service->store([
            'name' => 'Cerchio Test 1',
            'group_id' => $group->id,
        ]);

        $this->nextRound();

        $circles_service->store([
            'name' => 'Cerchio Test 2',
            'group_id' => $group->id,
        ]);

        $this->nextRound();

        return $group;
    }

    public function test_create()
    {
        $group = $this->createBasicGroup([
            'context' => 'user',
            'cardinality' => 'single',
            'filters_orders' => true,
        ]);

        $this->assertFalse(is_null($group));
        $this->assertEquals('Gruppo Test', $group->name);
        $this->assertEquals(2, $group->circles()->count());
        $this->assertEquals(1, $this->userAdmin->circles()->count());
    }

    private function attachModifier($circle, $amount)
    {
        $this->actingAs($this->userAdmin);

        $mod = ModifierType::find('spese-trasporto');
        $request = LarastrapStack::autoreadRender('modifiertype.edit', ['modtype' => $mod]);
        $request = array_merge($request, [
            'classes' => ['App\Booking', 'App\Product', 'App\Circle'],
        ]);

        app()->make('ModifierTypesService')->store($request);

        $this->nextRound();

        $test_shipping_value = 10;
        $modifiers = $circle->applicableModificationTypes();
        $modifier = null;

        foreach ($modifiers as $mod) {
            if ($mod->id == 'spese-trasporto') {
                $mod = $circle->modifiers()->where('modifier_type_id', $mod->id)->first();
                app()->make('ModifiersService')->update($mod->id, [
                    'value' => 'absolute',
                    'arithmetic' => 'sum',
                    'scale' => 'minor',
                    'applies_type' => 'none',
                    'applies_target' => 'booking',
                    'distribution_type' => 'none',
                    'simplified_amount' => $test_shipping_value,
                ]);

                $modifier = $mod;
                break;
            }
        }

        $this->assertNotNull($modifier);

        $this->nextRound();

        return $modifier;
    }

    public function test_user_modifiers()
    {
        $group = $this->createBasicGroup([
            'context' => 'user',
            'cardinality' => 'single',
            'filters_orders' => false,
        ]);

        $right_circle = $group->circles()->where('is_default', false)->first();
        $wrong_circle = $group->circles()->where('circles.id', '!=', $right_circle->id)->first();

        $order = $this->initOrder(null);
        $this->populateOrder($order);

        $this->nextRound();

        $target_user = $this->users->first();
        $target_user->circles()->sync([$right_circle->id]);

        $test_shipping_value = 10;
        $mod = $this->attachModifier($right_circle, $test_shipping_value);

        $order = app()->make('OrdersService')->show($order->id);
        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->price, 0.0);
        $checked = false;

        foreach ($order->bookings as $booking) {
            $mods = $booking->applyModifiers($redux, true);

            if ($booking->user->id != $target_user->id) {
                $this->assertEquals($mods->count(), 0);
            }
            else {
                $this->assertEquals($mods->count(), 1);

                foreach ($mods as $m) {
                    $this->assertEquals($m->effective_amount, $test_shipping_value);
                    $this->assertEquals($m->modifier_id, $mod->id);
                }

                $checked = true;
            }
        }

        $this->assertTrue($checked);
    }

    public function test_booking_modifiers()
    {
        $group = $this->createBasicGroup([
            'context' => 'booking',
        ]);

        $right_circle = $group->circles()->where('is_default', false)->first();
        $wrong_circle = $group->circles()->where('circles.id', '!=', $right_circle->id)->first();

        $order = $this->initOrder(null);
        $this->populateOrder($order);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($order->id);

        do {
            $target_bookings = [];

            foreach ($order->bookings as $booking) {
                if (rand() % 2 == 0) {
                    $booking->circles()->sync([$right_circle->id]);
                    $target_bookings[] = $booking->id;
                }
                else {
                    $booking->circles()->sync([$wrong_circle->id]);
                }
            }
        }
        while (empty($target_bookings) && count($target_bookings) != $order->bookings->count());

        $test_shipping_value = 10;
        $mod = $this->attachModifier($right_circle, $test_shipping_value);

        $order = app()->make('OrdersService')->show($order->id);
        $redux = $order->aggregate->reduxData();
        $this->assertNotEquals($redux->price, 0.0);
        $checked = false;

        foreach ($order->bookings as $booking) {
            $mods = $booking->applyModifiers($redux, true);

            if (in_array($booking->id, $target_bookings) == false) {
                $this->assertEquals($mods->count(), 0);
            }
            else {
                $this->assertEquals($mods->count(), 1);

                foreach ($mods as $m) {
                    $this->assertEquals($m->effective_amount, $test_shipping_value);
                    $this->assertEquals($m->modifier_id, $mod->id);
                }

                $checked = true;
            }
        }

        $this->assertTrue($checked);
    }

    public function test_associate_order()
    {
        $group = $this->createBasicGroup([
            'context' => 'user',
            'cardinality' => 'single',
            'filters_orders' => true,
        ]);

        $right_circle = $group->circles()->first();
        $wrong_circle = $group->circles()->where('circles.id', '!=', $right_circle->id)->first();

        $order = $this->initOrder(null);

        $this->actingAs($this->userReferrer);
        app()->make('OrdersService')->update($order->id, [
            'circles' => [$right_circle->id],
        ]);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($order->id);
        $this->assertEquals(1, $order->circles()->count());
        $this->assertEquals($right_circle->id, $order->circles()->first()->id);

        $this->nextRound();

        $user_yes = $this->createRoleAndUser($this->gas, 'supplier.book');
        $user_yes->circles()->sync([$right_circle->id]);

        $user_no = $this->createRoleAndUser($this->gas, 'supplier.book');
        $user_no->circles()->sync([$wrong_circle->id]);

        $this->nextRound();

        $this->actingAs($user_yes);
        $orders = getOrdersByStatus($user_yes, 'open');
        $this->assertEquals(1, $orders->count());

        $this->nextRound();

        $this->actingAs($user_no);
        $orders = getOrdersByStatus($user_no, 'open');
        $this->assertEquals(0, $orders->count());
    }
}
