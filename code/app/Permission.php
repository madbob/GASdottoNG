<?php

namespace app;

use Illuminate\Database\Eloquent\Model;

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

    /*
        Qui sono mappate tutte le possibili autorizzazioni del sistema,
        divise per classe di riferimento. Le funzioni di controllo sono
        per lo più implementate in App\AllowableTrait, tratto
        implementato appunto dalle suddette classi per le quali delle
        autorizzazioni sono mappate
    */
    public static function allPermissions()
    {
        return [
            'App\Gas' => [
                'gas.super' => 'Amministrazione totale (tutti i permessi)',
                'gas.permissions' => 'Modificare tutti i permessi',
                'gas.config' => 'Modificare le configurazioni del GAS',
                'supplier.add' => 'Creare nuovi fornitori',
                'users.admin' => 'Amministrare gli utenti',
                'users.view' => 'Vedere tutti gli utenti',
                'movements.view' => 'Vedere i movimenti contabili',
                'movements.admin' => 'Amministrare i movimenti contabili',
                'categories.admin' => 'Amministrare le categorie',
                'measures.admin' => 'Amministrare le unità di misura',
                'gas.statistics' => 'Visualizzare le statistiche',
                'notifications.admin' => 'Amministrare le notifiche',
            ],
            'App\Supplier' => [
                'supplier.modify' => 'Modificare il fornitore',
                'supplier.orders' => 'Aprire e modificare ordini',
                'supplier.book' => 'Effettuare ordini',
                'supplier.shippings' => 'Effettuare le consegne',
            ],
        ];
    }

    public static function classByRule($rule_id)
    {
        $all_permissions = self::allPermissions();
        foreach ($all_permissions as $class => $rules) {
            foreach ($rules as $identifier => $name) {
                if ($rule_id == $identifier) {
                    return $class;
                }
            }
        }

        return null;
    }
}
