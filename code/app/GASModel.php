<?php

namespace App;

use Auth;
use URL;
use Schema;

trait GASModel
{
    private $inner_runtime_cache;

    public function printableName()
    {
        return $this->name;
    }

    public function getPrintableNameAttribute()
    {
        return $this->printableName();
    }

    public function printableHeader()
    {
        $ret = $this->printableName();
        $icons = $this->icons();

        if (!empty($icons)) {
            $ret .= '<div class="pull-right">';

            foreach ($icons as $i) {
                $ret .= '<span class="glyphicon glyphicon-'.$i.'" aria-hidden="true"></span>&nbsp;';
            }

            $ret .= '</div>';
        }

        return $ret;
    }

    public function printableDate($name)
    {
        if ($this->$name == null) {
            return 'Mai';
        }
        else {
            $t = strtotime($this->$name);
            return ucwords(strftime('%A %d %B %G', $t));
        }
    }

    protected function innerCache($name, $function)
    {
        if (!isset($this->inner_runtime_cache[$name]))
            $this->inner_runtime_cache[$name] = $function($this);
        return $this->inner_runtime_cache[$name];
    }

    protected function setInnerCache($name, $value)
    {
        $this->inner_runtime_cache[$name] = $value;
    }

    private function relatedController()
    {
        $class = get_class($this);
        list($namespace, $class) = explode('\\', $class);

        return str_plural($class).'Controller';
    }

    public function getDisplayURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@index', $controller);

        return URL::action($action).'#'.$this->id;
    }

    public function getShowURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@show', $controller);

        return URL::action($action, $this->id);
    }

    public function getROShowURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@show_ro', $controller);

        try {
            return URL::action($action, $this->id);
        }
        catch(\Exception $e) {
            return null;
        }
    }

    public function testAndSet($request, $name, $field = null)
    {
        if ($field == null)
            $field = $name;

        if ($request->has($name))
            $this->$field = $request->input($name);
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public static function commonClassName()
    {
        return 'Oggetto';
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public function getPermissionsProxies()
    {
        return null;
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public function scopeEnabled($query)
    {
        return $query->whereNotNull('id');
    }

    public function scopeSorted($query)
    {
        if (Schema::hasColumn($this->table, 'name'))
            return $query->orderBy('name', 'asc');
        else if (Schema::hasColumn($this->table, 'lastname'))
            return $query->orderBy('lastname', 'asc');
        else
            return $query->orderBy('id', 'asc');
    }

    public static function iconsMap()
    {
        static $icons = null;

        if ($icons == null) {
            $user = Auth::user();

            /*
                La chiave di ogni array interno è il nome dell'icona FontAwesome
                da usare per la relativa icona. Usare un nome non esistente per
                avere il filtro ma non l'icona (lasciare un punto '.' davanti
                per indicare che è una azione deliberata)
            */
            $icons = [
                'Supplier' => [
                    'pencil' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.modify', $obj);
                        },
                        'text' => _i('Puoi modificare il fornitore'),
                    ],
                    'th-list' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.orders', $obj);
                        },
                        'text' => _i('Puoi aprire nuovi ordini per il fornitore'),
                    ],
                    'arrow-down' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.shippings', $obj);
                        },
                        'text' => _i('Gestisci le consegne per il fornitore'),
                    ],
                ],
                'Product' => [
                    'off' => (object) [
                        'test' => function ($obj) {
                            return $obj->active == false;
                        },
                        'text' => _i('Disabilitato'),
                    ],
                    '.on' => (object) [
                        'test' => function ($obj) {
                            return $obj->active == true;
                        },
                        'text' => _i('Attivo'),
                    ],
                    'star' => (object) [
                        'test' => function ($obj) {
                            return !empty($obj->discount) && $obj->discount != 0;
                        },
                        'text' => _i('Scontato'),
                    ],
                ],
                'Aggregate' => [
                    'th-list' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.orders', $obj);
                        },
                        'text' => _i('Puoi modificare'),
                    ],
                    'arrow-down' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.shippings', $obj);
                        },
                        'text' => _i('Gestisci le consegne'),
                    ],
                    'play' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'open';
                        },
                        'text' => _i('Prenotazioni Aperte'),
                    ],
                    'pause' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'suspended';
                        },
                        'text' => _i('In Sospeso'),
                    ],
                    'stop' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'closed';
                        },
                        'text' => _i('Prenotazioni Chiuse'),
                    ],
                    'step-forward' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'shipped';
                        },
                        'text' => _i('Consegnato'),
                    ],
                    'eject' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'archived';
                        },
                        'text' => _i('Archiviato'),
                    ],
                ],
                'Order' => [
                    'th-list' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.orders', $obj);
                        },
                        'text' => _i("Puoi modificare l'ordine"),
                    ],
                    'arrow-down' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.shippings', $obj);
                        },
                        'text' => _i("Gestisci le consegne per l'ordine"),
                    ],
                    'play' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'open';
                        },
                        'text' => _i('Prenotazioni Aperte'),
                    ],
                    'pause' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'suspended';
                        },
                        'text' => _i('In Sospeso'),
                    ],
                    'stop' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'closed';
                        },
                        'text' => _i('Prenotazioni Chiuse'),
                    ],
                    'step-forward' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'shipped';
                        },
                        'text' => _i('Consegnato'),
                    ],
                    'eject' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'archived';
                        },
                        'text' => _i('Archiviato'),
                    ],
                ],
                'AggregateBooking' => [
                    'time' => (object) [
                        'test' => function ($obj) {
                            return $obj->status != 'shipped';
                        },
                        'text' => _i('Da consegnare'),
                    ],
                    'ok' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'shipped';
                        },
                        'text' => _i('Consegnato'),
                    ],
                    'download-alt' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'saved';
                        },
                        'text' => _i('Salvato'),
                    ],
                ],
                'Invoice' => [
                    'time' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'pending';
                        },
                        'text' => _i('In Attesa'),
                    ],
                    'pushpin' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'to_verify';
                        },
                        'text' => _i('Da Verificare'),
                    ],
                    'search' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'verified';
                        },
                        'text' => _i('Verificata'),
                    ],
                    'ok' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'payed';
                        },
                        'text' => _i('Pagata'),
                    ],
                ],
                'User' => [
                ],
            ];

            if ($user->can('supplier.add', $user->gas)) {
                $icons['Supplier']['off'] = (object) [
                    'test' => function ($obj) {
                        return $obj->deleted_at != null;
                    },
                    'text' => _i('Eliminato'),
                ];
            }

            if ($user->can('users.admin', $user->gas)) {
                $icons['User']['thumbs-down'] = (object) [
                    'test' => function ($obj) {
                        return $obj->suspended == true;
                    },
                    'text' => _i('Sospeso'),
                ];

                $icons['User']['off'] = (object) [
                    'test' => function ($obj) {
                        return ($obj->suspended == false && $obj->deleted_at != null);
                    },
                    'text' => _i('Cessato'),
                ];
            }

            if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas)) {
                $icons['User']['ban-circle'] = (object) [
                    'test' => function ($obj) {
                        return $obj->current_balance_amount < 0;
                    },
                    'text' => _i('Credito < 0'),
                ];

                /*
                    Se la gestione delle quote di iscrizione è abilitata, viene
                    attivata la relativa icona per distinguere gli utenti che non
                    l'hanno pagata o rinnovata
                */
                if ($user->gas->getConfig('annual_fee_amount') != 0) {
                    $icons['User']['euro'] = (object) [
                        'test' => function ($obj) {
                            return $obj->fee_id == 0;
                        },
                        'text' => _i('Quota non Pagata'),
                    ];
                }
            }

            /*
                Questo è per generare le icone dei ruoli degli utenti comuni
            */
            $roles = Role::where('id', '!=', $user->gas->roles['user'])->where('id', '!=', $user->gas->roles['friend'])->get();
            foreach($roles as $index => $role) {
                $icons['User']['king' . $index] = (object) [
                    'test' => function($obj) use ($role) {
                        foreach($obj->roles as $r)
                            if ($r->id == $role->id)
                                return true;
                        return false;
                    },
                    'text' => $role->name
                ];
            }
        }

        return $icons;
    }

    public function icons()
    {
        $class = get_class($this);
        list($namespace, $class) = explode('\\', $class);

        $map = self::iconsMap();
        $ret = [];

        if (isset($map[$class])) {
            foreach ($map[$class] as $icon => $condition) {
                $t = $condition->test;
                if ($t($this)) {
                    $ret[] = $icon;
                }
            }
        }

        return $ret;
    }

    public static function iconsLegend($class)
    {
        $map = self::iconsMap();
        $ret = [];

        if (isset($map[$class])) {
            foreach ($map[$class] as $icon => $condition) {
                $ret[$icon] = $condition->text;
            }
        }

        return $ret;
    }
}
