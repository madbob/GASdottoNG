<?php

namespace App;

trait GASModel
{
	public function printableName()
	{
		return $this->name;
	}

	public function printableHeader()
	{
		return $this->printableName();
	}

	public function printableDate($name)
	{
		$t = strtotime($this->$name);
		return ucwords(strftime('%A %d %B %G', $t));
	}
}
