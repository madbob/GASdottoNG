<?php

namespace Tests;

use App\User;
use App\Models\Concerns\CreditableTrait;

class ReflectionsTest extends TestCase
{
    public function test_traits_detection()
    {
        $user = User::inRandomOrder()->first();
        $this->assertTrue(hasTrait($user, CreditableTrait::class));

        $creditables = modelsUsingTrait(CreditableTrait::class);
        $this->assertTrue(isset($creditables['App\User']));
    }

    public function test_normalize()
    {
        $user = User::inRandomOrder()->first();
        $this->assertEquals($user->id, normalizeId($user));
        $this->assertEquals($user->id, normalizeId($user->id));
    }

    public function test_inlining_model()
    {
        $user = User::inRandomOrder()->first();
        $inline = inlineId($user);
        $test = fromInlineId($inline);
        $this->assertEquals($user->id, $test->id);
    }
}
