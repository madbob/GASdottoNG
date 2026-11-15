<?php

namespace Tests\Features;

use Tests\TestCase;

use App\View\Texts\Modifier as ModifierTexts;

class ModifiersTest extends TestCase
{
    public function test_strings()
    {
        $models = modelsUsingTrait('App\Models\Concerns\ModifiableTrait');
        $this->assertFalse(count($models) === 0);

        foreach($models as $modelClass => $modelName) {
            $target = new $modelClass();
            $labels = ModifierTexts::descriptions($target);
            $this->assertFalse(count($labels) === 0);

            foreach($labels as $texts) {
                foreach($texts as $text) {
                    $this->assertFalse(str_starts_with($text, 'texts.'));
                }
            }
        }
    }
}
