<?php

namespace App\Printers\Components;

interface Component
{
    public function renderHtml();
    public function renderCsv();
}
