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

	static public function iconsMap()
	{
		return [
			'User' => [
				'king' => (object)[
					'test' => function($obj) {
						return $obj->gas->userHas('gas.super', $obj);
					},
					'text' => 'Utente amministratore'
				],
			],
			'Supplier' => [
				'pencil' => (object)[
					'test' => function($obj) {
						return $obj->userCan('supplier.modify');
					},
					'text' => 'Puoi modificare il fornitore'
				],
				'th-list' => (object)[
					'test' => function($obj) {
						return $obj->userCan('supplier.orders');
					},
					'text' => 'Puoi aprire nuovi ordini per il fornitore'
				],
				'arrow-down' => (object)[
					'test' => function($obj) {
						return $obj->userCan('supplier.shippings');
					},
					'text' => 'Gestisci le consegne per il fornitore'
				],
			],
			'Aggregate' => [
				'th-list' => (object)[
					'test' => function($obj) {
						return $obj->userCan('supplier.orders');
					},
					'text' => 'Puoi modificare l\'ordine'
				],
				'arrow-down' => (object)[
					'test' => function($obj) {
						return $obj->userCan('supplier.shippings');
					},
					'text' => 'Gestisci le consegne per l\'ordine'
				],
				'play' => (object)[
					'test' => function($obj) {
						return ($obj->status == 'open');
					},
					'text' => 'Ordine aperto'
				],
				'pause' => (object)[
					'test' => function($obj) {
						return ($obj->status == 'suspended');
					},
					'text' => 'Ordine sospeso'
				],
				'stop' => (object)[
					'test' => function($obj) {
						return ($obj->status == 'closed');
					},
					'text' => 'Ordine chiuso'
				],
				'step-forward' => (object)[
					'test' => function($obj) {
						return ($obj->status == 'shipped');
					},
					'text' => 'Ordine consegnato'
				],
				'eject' => (object)[
					'test' => function($obj) {
						return ($obj->status == 'archived');
					},
					'text' => 'Ordine archiviato'
				],
			]
		];
	}

	public function icons()
	{
		$class = get_class($this);
		list($namespace, $class) = explode('\\', $class);

		$map = GASModel::iconsMap();
		$ret = [];

		if (isset($map[$class])) {
			foreach ($map[$class] as $icon => $condition) {
				$t = $condition->test;
				if ($t($this))
					$ret[] = $icon;
			}
		}

		return $ret;
	}

	static public function iconsLegend($class)
	{
		$map = GASModel::iconsMap();
		$ret = [];

		if (isset($map[$class])) {
			foreach ($map[$class] as $icon => $condition)
				$ret[$icon] = $condition->text;
		}

		return $ret;
	}
}
