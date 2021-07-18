<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Filler extends Component
{
    public $dataAction;
    public $dataFillTarget;
    public $downloadButtons;

    public function __construct($dataAction, $dataFillTarget, $downloadButtons = [])
    {
        $this->dataAction = $dataAction;
        $this->dataFillTarget = $dataFillTarget;
        $this->downloadButtons = $downloadButtons;
    }

    public function render()
    {
        return view('components.filler');
    }
}
