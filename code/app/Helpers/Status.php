<?php

namespace App\Helpers;

class Status
{
	public static function orders()
	{
		/*
			L'attributo "default_display" determina gli stati che vengono
			visualizzati di default quando viene chiesto l'elenco degli ordini.
			Cfr. defaultOrders()

			L'attributo "aggregate_priority" serve a determinare lo stato
			dell'aggregato dentro cui si trova l'ordine stesso: lo stato di
			priorità più bassa vince. Cfr. Aggregate::getStatusAttribute()
		*/

		$statuses = [];

		$statuses['open'] = (object) [
			'label' => _i('Prenotazioni Aperte'),
			'icon' => 'play',
			'default_display' => true,
			'aggregate_priority' => 1,
		];

		$statuses['closed'] = (object) [
			'label' => _i('Prenotazioni Chiuse'),
			'icon' => 'stop-fill',
			'default_display' => true,
			'aggregate_priority' => 2,
		];

		$statuses['shipped'] = (object) [
			'label' => _i('Consegnato'),
			'icon' => 'skip-forward',
			'default_display' => true,
			'aggregate_priority' => 4,
		];

		if (currentAbsoluteGas()->hasFeature('integralces')) {
			$statuses['user_payment'] = (object) [
				'label' => _i('Pagamento Utenti'),
				'icon' => 'cash',
				'default_display' => true,
				'aggregate_priority' => 3,
			];
		}

		$statuses['archived'] = (object) [
			'label' => _i('Archiviato'),
			'icon' => 'eject',
			'default_display' => false,
			'aggregate_priority' => 5,
		];

		$statuses['suspended'] = (object) [
			'label' => _i('In Sospeso'),
			'icon' => 'pause',
			'default_display' => true,
			'aggregate_priority' => 0,
		];

		return $statuses;
	}

	public static function invoices()
	{
		return [
			'pending' => _i('In Attesa'),
			'to_verify' => _i('Da Verificare'),
			'verified' => _i('Verificata'),
			'payed' => _i('Pagata'),
		];
	}
}
