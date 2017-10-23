<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Notifications\ResetPasswordNotification;

use Auth;

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

    public static function commonClassName()
    {
        return 'Utente';
    }

    public function gas()
    {
        return $this->belongsTo('App\Gas');
    }

    public function roles($target = null)
    {
        return $this->belongsToMany('App\Role')->orderBy('name', 'asc')->withPivot('id');
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
        return $this->lastname.' '.$this->firstname;
    }

    public function addRole($role, $assigned)
    {
        $test = $this->roles()->where('roles.id', $role->id)->first();
        if ($test == null) {
            $this->roles()->attach($role->id);
            $test = $this->roles()->where('roles.id', $role->id)->first();
        }

        if ($assigned)
            $test->attachApplication($assigned);
    }

    public function removeRole($role, $assigned)
    {
        $test = $this->roles()->where('roles.id', $role->id)->first();
        if ($test == null)
            return;

        if ($assigned) {
            $test->detachApplication($assigned);
            if (empty($test->applications(true))) {
                $this->roles()->detach($role->id);
            }
        }
        else {
            $this->roles()->detach($role->id);
        }
    }

    public function getPendingBalanceAttribute()
    {
        $bookings = $this->bookings()->where('status', 'pending')->get();
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
            'bank' => 'Credito',
        ];
    }
}
