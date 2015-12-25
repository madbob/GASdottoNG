<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Movement extends Model
{
	use GASModel;

	public function printableName()
	{
		return sprintf('%s | %f €', $this->printableDate('created_at'), $this->amount);
	}
}
