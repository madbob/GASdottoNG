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
use App\PayableTrait;

class User extends Authenticatable
{
    use Notifiable, Authorizable, SoftDeletes, ContactableTrait, CreditableTrait, PayableTrait, GASModel, SluggableID;

    public $incrementing = false;
    protected $table = 'users';
    protected $hidden = ['password', 'remember_token'];
    protected $dates = ['deleted_at'];

    protected $events = [
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

    public function getSlugID()
    {
        return $this->username;
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

    public function printableName()
    {
        $ret = $this->lastname . ' ' . $this->firstname;

        if (empty(trim($ret)))
            $ret = $this->username;

        return $ret;
    }

    public function printableHeader()
    {
        $ret = $this->printableName();

        if ($this->isFriend() == false) {
            $icons = $this->icons();

            if (!empty($icons)) {
                $ret .= '<div class="pull-right">';

                foreach ($icons as $i) {
                    $ret .= '<span class="glyphicon glyphicon-'.$i.'" aria-hidden="true"></span>&nbsp;';
                }

                $ret .= '</div>';
            }
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
            $tot += $order->userBooking($this->id)->total_value;

        if ($tot != 0)
            $ret .= '<div class="pull-right">' . _i('Ha ordinato %s', printablePriceCurrency($tot)) . '</div>';

        return $ret;
    }

    public function isFriend()
    {
        return $this->parent_id != null;
    }

    public function addRole($role, $assigned)
    {
        $role_id = normalizeId($role);

        $test = $this->roles()->where('roles.id', $role_id)->first();
        if ($test == null) {
            $this->roles()->attach($role_id);
            $test = $this->roles()->where('roles.id', $role_id)->first();
        }

        if ($assigned)
            $test->attachApplication($assigned);
    }

    public function removeRole($role, $assigned)
    {
        $role_id = normalizeId($role);

        $test = $this->roles()->where('roles.id', $role_id)->first();
        if ($test == null)
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

    public function getPendingBalanceAttribute()
    {
        $bookings = $this->bookings()->where('status', 'pending')->whereHas('order', function($query) {
            $query->whereIn('status', ['open', 'closed']);
        })->get();

        $value = 0;

        foreach($bookings as $b)
            $value += $b->total_value;

        return $value;
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

    /******************************************************** CreditableTrait */

    public static function balanceFields()
    {
        return [
            'bank' => _i('Credito'),
        ];
    }
}
