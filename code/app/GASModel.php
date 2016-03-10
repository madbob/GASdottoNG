<?php

namespace App;

use URL;

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

	private function relatedController()
	{
		$class = get_class($this);
		list($namespace, $class) = explode('\\', $class);
		return str_plural($class) . 'Controller';
	}

	public function getDisplayURL()
	{
		$controller = $this->relatedController();
		$action = sprintf('%s@index', $controller);
		return URL::action($action) . '#' . $this->id;
	}

	public function getShowURL()
	{
		$controller = $this->relatedController();
		$action = sprintf('%s@show', $controller);
		return URL::action($action, $this->id);
	}
}
