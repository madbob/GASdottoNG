<?php

namespace App\Printers\Components;

class Title implements Component
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function renderHtml()
    {
        return sprintf('<h3>%s</h3>', $this->text);
    }

    public function renderCsv()
    {
        return null;
    }
}
