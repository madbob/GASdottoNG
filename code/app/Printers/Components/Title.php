<?php

/*
    Nel contesto di un Document, questo rappresenta l'intestazione principale.
    Nota bene: questo contenuto non viene esportato nei CSV, per ridurre la
    quantità di formattazione in file che devono essere formattati il meno
    possibile
*/

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
