<?php

namespace Tests;

use App\User;
use App\Models\Concerns\CreditableTrait;

class ReflectionsTest extends TestCase
{
    public function testTraitsDetection()
    {
        $user = User::inRandomOrder()->first();
        $this->assetTrue(hasTrait($user, CreditableTrait::class));

        $creditables = modelsUsingTrait(CreditableTrait::class);
        $this->assertTrue(isset($creditables['App\User']));
    }

    public function testNormalize()
    {
        $user = User::inRandomOrder()->first();
        $this->assertEquals($user->id, normalizeId($user));
        $this->assertEquals($user->id, normalizeId($user->id));
    }

    function testInliningModel()
    {
        $user = User::inRandomOrder()->first();
        $inline = inlineId($user);
        $test = fromInlineId($inline);
        $this->assertEquals($user->id, $test->id);
    }
}
