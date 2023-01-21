<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Filler extends Component
{
    public $dataAction;
    public $dataFillTarget;
	public $actionButtons;
    public $downloadButtons;

    public function __construct($dataAction, $dataFillTarget, $downloadButtons = [], $actionButtons = [])
    {
        $this->dataAction = $dataAction;
        $this->dataFillTarget = $dataFillTarget;
		$this->actionButtons = $actionButtons;
        $this->downloadButtons = $downloadButtons;
    }

    public function render()
    {
        return view('components.filler');
    }
}
