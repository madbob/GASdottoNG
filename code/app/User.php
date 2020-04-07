<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Notifications\ResetPasswordNotification;

use Auth;
use URL;

use App\Events\SluggableCreating;

use App\GASModel;
use App\SluggableID;
use App\ContactableTrait;
use App\SuspendableTrait;
use App\PayableTrait;
use App\Role;
use App\Order;

class User extends Authenticatable
{
    use Notifiable, Authorizable, SoftDeletes, ContactableTrait, CreditableTrait, PayableTrait, SuspendableTrait, GASModel, SluggableID;

    public $incrementing = false;
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

        $user = Auth::user();
        if ($user != null) {
            $gas_id = $user->gas->id;

            static::addGlobalScope('gas', function (Builder $builder) use ($gas_id) {
                $builder->where('gas_id', $gas_id);
            });
        }
    }

    public static function commonClassName()
    {
        return _i('Utente');
    }

    public function gas()
    {
        return $this->belongsTo('App\Gas');
    }

    public function roles($target = null)
    {
        return $this->belongsToMany('App\Role')->orderBy('name', 'asc')->withPivot('id');
    }

    public function friends()
    {
        return $this->hasMany('App\User', 'parent_id');
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
        if ($this->isFriend())
            return URL::action('FriendsController@show', $this->id);
        else
            return URL::action('UsersController@show', $this->id);
    }

    public function printableFriendHeader($aggregate)
    {
        $ret = $this->printableName();

        $tot = 0;
        foreach($aggregate->orders as $order)
            $tot += $order->userBooking($this->id)->getValue('effective', false);

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

    public function getPendingBalanceAttribute()
    {
        $bookings = $this->bookings()->where('status', 'pending')->whereHas('order', function($query) {
            $query->whereIn('status', ['open', 'closed']);
        })->get();

        $value = 0;

        foreach($bookings as $b) {
            $value += $b->getValue('effective', true);
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
            'username' => (object) [
                'name' => _i('Username'),
            ],
            'email' => (object) [
                'name' => _i('E-Mail'),
                'checked' => true,
            ],
            'phone' => (object) [
                'name' => _i('Telefono'),
                'checked' => true,
            ],
            'mobile' => (object) [
                'name' => _i('Cellulare'),
                'checked' => true,
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
                        $ret[] = join(', ', $contacts);
                        break;
                    case 'shipping_place':
                        $sp = $this->shippingplace;
                        if ($sp)
                            $ret[] = $sp->name;
                        else
                            $ret[] = _i('Nessuno');
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
