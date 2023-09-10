<?php

namespace App\Printers\Components;

class Header implements Component
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function renderHtml()
    {
        return sprintf('<h4>%s</h4>', $this->text);
    }

    public function renderCsv()
    {
        return [[$this->text]];
    }
}
