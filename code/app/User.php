<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Auth;
use Log;
use App;
use URL;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\ManualWelcomeMessage;
use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Authorizable, CanResetPassword, SoftDeletes,
        ContactableTrait, CreditableTrait, PayableTrait, SuspendableTrait, FriendTrait, HierarcableTrait, RoleableTrait, BookerTrait,
        GASModel, SluggableID, Cachable;

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

    public function parent()
    {
        return $this->belongsTo('App\User', 'parent_id');
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
            $ret = paymentMethodByType($obj->payment_method_id);

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

    public function testUserAccess($myself = null)
    {
        if (is_null($myself)) {
            $myself = Auth::user();
        }

        if ($myself->id == $this->id) {
            return true;
        }

        if ($this->parent_id == $myself->id && $myself->can('users.subusers', $myself->gas)) {
            return true;
        }

        return false;
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

    public function initialWelcome()
    {
        $this->load('contacts');

        if (!empty($this->getContactsByType('email'))) {
            $this->enforce_password_change = true;
            $this->access_token = Str::random(10);
            $this->save();
            $this->notify(new ManualWelcomeMessage($this->access_token));
        }
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        return $this->username;
    }

    /***************************** CreditableTrait (ereditato da BookerTrait) */

    public function balanceFields()
    {
        return [
            'bank' => _i('Credito'),
        ];
    }
}
