<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Permission extends Model
{
	protected $fillable = ['action', 'user_id'];

	public function user()
	{
		return $this->belongsTo('App\User');
	}

	public function target()
	{
		return $this->morphsTo();
	}

	public static function allPermissions()
	{
		return [
			'Gas' => [
				'gas.permissions'	=> 'Modificare tutti i permessi',
				'gas.config'		=> 'Modificare le configurazioni del GAS',
				'supplier.add'		=> 'Creare nuovi fornitori',
				'gas.statistics'	=> 'Visualizzare le statistiche',
				'users.admin'		=> 'Amministrare gli utenti',
				'users.view'		=> 'Vedere tutti gli utenti',
				'movements.view'	=> 'Vedere i movimenti contabili',
				'movements.admin'	=> 'Amministrare i movimenti contabili',
				'notifications.admin'	=> 'Amministrare le notifiche',
			],
			'Supplier' => [
				'supplier.modify'	=> 'Modificare il fornitore',
				'supplier.orders'	=> 'Aprire e modificare ordini',
				'supplier.shippings'	=> 'Effettuare le consegne',
			]
		];
	}
}
