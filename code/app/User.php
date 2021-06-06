<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Auth;
use Log;
use App;
use URL;

use App\Notifications\ResetPasswordNotification;
use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Authorizable, CanResetPassword, SoftDeletes, ContactableTrait, CreditableTrait, PayableTrait, SuspendableTrait, HierarcableTrait, GASModel, SluggableID;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $hidden = ['password', 'remember_token'];
    protected $dates = ['deleted_at'];

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected $casts = [
        'rid' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new RestrictedGAS());
    }

    public static function commonClassName()
    {
        return _i('Utente');
    }

    public function roles($target = null)
    {
        return $this->belongsToMany('App\Role')->orderBy('name', 'asc')->withPivot('id');
    }

    public function friends()
    {
        return $this->hasMany('App\User', 'parent_id');
    }

    public function friends_with_trashed()
    {
        return $this->hasMany('App\User', 'parent_id')->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo('App\User', 'parent_id');
    }

    public function getManagedRolesAttribute()
    {
        return Role::sortedByHierarchy(true);
    }

    public function notifications()
    {
        return $this->belongsToMany('App\Notification')->withPivot('done')->where('notification_user.done', '=', false)->orderBy('start_date', 'desc');
    }

    public function suppliers()
    {
        return $this->belongsToMany('App\Supplier');
    }

    public function allnotifications()
    {
        return $this->belongsToMany('App\Notification')->orderBy('start_date', 'desc');
    }

    public function deposit()
    {
        return $this->belongsTo('App\Movement');
    }

    public function fee()
    {
        return $this->belongsTo('App\Movement');
    }

    public function bookings()
    {
        return $this->hasMany('App\Booking')->orderBy('created_at', 'desc');
    }

    public function shippingplace()
    {
        return $this->belongsTo('App\Delivery', 'preferred_delivery_id');
    }

    public function scopeEnabled($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeFilterEnabled($query)
    {
        $user = Auth::user();
        if ($user->can('users.admin', $user->gas))
            return $query->withTrashed();
        else
            return $query;
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('lastname', 'asc')->orderBy('firstname', 'asc');
    }

    public function scopeTopLevel($query)
    {
        return $query->where('parent_id', null);
    }

    public function getPaymentMethodAttribute()
    {
        return $this->innerCache('payment_method', function($obj) {
            $ret = MovementType::paymentMethodByType($obj->payment_method_id);

            if (!$ret) {
                $ret = (object) [
                    'name' => _i('Non Specificato'),
                    'valid_config' => function($target) {
                        return true;
                    }
                ];
            }

            $ret->id = $this->payment_method_id;
            return $ret;
        });
    }

    public function printableName()
    {
        $ret = $this->lastname . ' ' . $this->firstname;

        if (empty(trim($ret)))
            $ret = $this->username;

        return $ret;
    }

    public function getLastBookingAttribute()
    {
        $last = $this->bookings()->first();
        if ($last == null)
            return null;
        else
            return $last->created_at;
    }

    public function canBook()
    {
        if ($this->gas->restrict_booking_to_credit) {
            if ($this->isFriend()) {
                return $this->parent->canBook();
            }
            else {
                return $this->activeBalance() > 0;
            }
        }
        else {
            return true;
        }
    }

    public function printableHeader()
    {
        $ret = $this->printableName();

        if ($this->isFriend() == false) {
            $ret .= $this->headerIcons();
        }

        return $ret;
    }

    public function getShowURL()
    {
        return URL::action('UsersController@show', $this->id);
    }

    public function printableFriendHeader($aggregate)
    {
        $ret = $this->printableName();

        $tot = 0;
        foreach($aggregate->orders as $order)
            $tot += $order->userBooking($this)->getValue('effective', false);

        if ($tot != 0)
            $ret .= '<div class="pull-right">' . _i('Ha ordinato %s', printablePriceCurrency($tot)) . '</div>';

        return $ret;
    }

    public function isFriend()
    {
        return $this->parent_id != null;
    }

    public function testUserAccess()
    {
        $myself = Auth::user();

        if ($myself->id == $this->id)
            return true;

        if ($this->parent_id == $myself->id && $myself->can('users.subusers', $myself->gas))
            return true;

        return false;
    }

    public function addRole($role, $assigned)
    {
        $role_id = normalizeId($role);

        $test = $this->roles()->where('roles.id', $role_id)->first();
        if (is_null($test)) {
            $this->roles()->attach($role_id);
            $test = $this->roles()->where('roles.id', $role_id)->first();
        }

        if (is_null($test)) {
            Log::error('Impossibile aggiungere ruolo ' . $role_id . ' a utente ' . $this->id);
        }
        else {
            if ($assigned)
                $test->attachApplication($assigned);
        }

        return $test;
    }

    public function removeRole($role, $assigned)
    {
        $role_id = normalizeId($role);

        $test = $this->roles()->where('roles.id', $role_id)->first();
        if (is_null($test))
            return;

        if ($assigned) {
            $test->detachApplication($assigned);
            if ($test->applications(true)->isEmpty()) {
                $this->roles()->detach($role_id);
            }
        }
        else {
            $this->roles()->detach($role_id);
        }
    }

    public function targetsByAction($action, $exclude_trashed = true)
    {
        $targets = [];
        $class = Role::classByRule($action);

        foreach ($this->roles as $role) {
            if ($role->enabledAction($action))
                foreach($role->applications(true, $exclude_trashed) as $app)
                    if ($class == null || get_class($app) == $class)
                        $targets[$app->id] = $app;
        }

        return $targets;
    }

    /*
        Questa funzione ritorna la cifra dovuta dall'utente per le prenotazioni
        fatte dall'utente e non ancora pagate, ma senza considerare anche gli
        eventuali amici
    */
    public function getPendingBalanceAttribute()
    {
        $bookings = $this->bookings()->where('status', 'pending')->whereHas('order', function($query) {
            $query->whereIn('status', ['open', 'closed']);
        })->get();

        $value = 0;

        foreach($bookings as $b) {
            $value += $b->getValue('effective', false);
        }

        return $value;
    }

    public function activeBalance()
    {
        if ($this->isFriend()) {
            return $this->parent->activeBalance();
        }
        else {
            $current_balance = $this->current_balance_amount;
            $to_pay = $this->pending_balance;

            foreach($this->friends as $friend) {
                $tpf = $friend->pending_balance;
                $to_pay += $tpf;
            }

            return $current_balance - $to_pay;
        }
    }

    public function getPictureUrlAttribute()
    {
        if (empty($this->picture))
            return '';
        else
            return url('users/picture/' . $this->id);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public static function usernamePattern()
    {
        return '[A-Za-z0-9_@.\- ]{1,50}';
    }

    public static function formattableColumns()
    {
        $ret = [
            'lastname' => (object) [
                'name' => _i('Cognome'),
                'checked' => true,
            ],
            'firstname' => (object) [
                'name' => _i('Nome'),
                'checked' => true,
            ],
            'fullname' => (object) [
                'name' => _i('Nome Completo'),
            ],
            'username' => (object) [
                'name' => _i('Username'),
            ],
            'email' => (object) [
                'name' => _i('E-Mail'),
            ],
            'phone' => (object) [
                'name' => _i('Telefono'),
            ],
            'mobile' => (object) [
                'name' => _i('Cellulare'),
            ],
            'address' => (object) [
                'name' => _i('Indirizzo'),
            ],
            'taxcode' => (object) [
                'name' => _i('Codice Fiscale'),
            ],
            'card_number' => (object) [
                'name' => _i('Numero Tessera'),
            ],
            'status' => (object) [
                'name' => _i('Stato'),
            ],
            'payment_method' => (object) [
                'name' => _i('Modalità Pagamento'),
            ],
        ];

        if (currentAbsoluteGas()->hasFeature('shipping_places')) {
            $ret['shipping_place'] = (object) [
                'name' => _i('Luogo di Consegna'),
                'checked' => true,
            ];
        }

        if (currentAbsoluteGas()->hasFeature('rid')) {
            $ret['rid->iban'] = (object) [
                'name' => _i('IBAN'),
                'checked' => true,
            ];
        }

        /*
            Se sono nel contesto di una richiesta non vincolata a nessun GAS
            dell'istanza (cfr. middleware ActIntoGas), permetto di filtrare gli
            utenti anche in base del GAS di appartenenza
        */
        if (App::make('GlobalScopeHub')->enabled() == false) {
            $ret['gas'] = (object) [
                'name' => _i('GAS'),
            ];
        }

        return $ret;
    }

    public function formattedFields($fields)
    {
        $ret = [];

        foreach($fields as $f) {
            try {
                switch($f) {
                    case 'email':
                    case 'phone':
                    case 'mobile':
                    case 'address':
                        $contacts = $this->getContactsByType($f);
                        if (!empty($contacts)) {
                            $ret[] = join(', ', $contacts);
                        }
                        else {
                            $ret[] = '';
                        }
                        break;

                    case 'fullname':
                        $ret[] = $this->printableName();
                        break;

                    case 'shipping_place':
                        $sp = $this->shippingplace;
                        if (is_null($sp)) {
                            $ret[] = _i('Nessuno');
                        }
                        else {
                            $ret[] = $sp->name;
                        }
                        break;

                    case 'status':
                        $ret[] = $this->printableStatus();
                        break;

                    case 'gas':
                        $ret[] = $this->gas->name;
                        break;

                    case 'payment_method':
                        $ret[] = $this->payment_method->name;
                        break;

                    default:
                        $ret[] = accessAttr($this, $f);
                        break;
                }
            }
            catch(\Exception $e) {
                Log::error('Esportazione CSV, impossibile accedere al campo ' . $f . ' di utente ' . $this->id);
                $ret[] = '';
            }
        }

        return $ret;
    }

    public static function unrollSpecialSelectors($users)
    {
        $map = [];

        if(!is_array($users)) {
            return $map;
        }

        foreach ($users as $u) {
            if (strrpos($u, 'special::', -strlen($u)) !== false) {
                if (strrpos($u, 'special::role::', -strlen($u)) !== false) {
                    $role_id = substr($u, strlen('special::role::'));
                    $role = Role::find($role_id);
                    foreach ($role->users as $u) {
                        $map[] = $u->id;
                    }
                }
                elseif (strrpos($u, 'special::order::', -strlen($u)) !== false) {
                    $order_id = substr($u, strlen('special::order::'));
                    $order = Order::findOrFail($order_id);
                    foreach ($order->topLevelBookings() as $booking) {
                        $map[] = $booking->user->id;
                    }
                }
            } else {
                $map[] = $u;
            }
        }

        return array_unique($map);
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        return $this->username;
    }

    /******************************************************** CreditableTrait */

    public static function balanceFields()
    {
        return [
            'bank' => _i('Credito'),
        ];
    }
}
