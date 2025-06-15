<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Auth;

use App\Models\Concerns\ContactableTrait;
use App\Models\Concerns\InCircles;
use App\Models\Concerns\PayableTrait;
use App\Models\Concerns\SuspendableTrait;
use App\Models\Concerns\HierarcableTrait;
use App\Models\Concerns\RoleableTrait;
use App\Models\Concerns\BookerTrait;
use App\Models\Concerns\PaysFees;
use App\Models\Concerns\TracksUpdater;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\ManualWelcomeMessage;
use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;

class User extends Authenticatable
{
    use Authorizable, BookerTrait, Cachable, CanResetPassword, ContactableTrait, GASModel,
        HasFactory, HierarcableTrait, InCircles, Notifiable, PayableTrait, PaysFees, RoleableTrait, SluggableID,
        SoftDeletes, SuspendableTrait, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $hidden = ['password', 'remember_token'];

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected $casts = [
        'rid' => 'array',
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->enableGlobalCache();
    }

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
        static::addGlobalScope(new RestrictedGAS());
    }

    public static function commonClassName()
    {
        return __('texts.user.name');
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany('App\Notification')->withPivot('done')->where('notification_user.done', '=', false)->orderBy('start_date', 'desc');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany('App\Supplier');
    }

    public function scopeFilterEnabled($query)
    {
        $user = Auth::user();
        if ($user->can('users.admin', $user->gas)) {
            return $query->withTrashed()->whereNull('deleted_at')->orWhereNull('suspended_at');
        }
        else {
            return $query;
        }
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('lastname', 'asc')->orderBy('firstname', 'asc');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getPaymentMethodAttribute()
    {
        return $this->innerCache('payment_method', function ($obj) {
            $ret = paymentMethodByType($obj->payment_method_id);

            if (! $ret) {
                $ret = (object) [
                    'name' => __('texts.generic.unspecified'),
                    'valid_config' => function ($target) {
                        return true;
                    },
                ];
            }

            $ret->id = $this->payment_method_id;

            return $ret;
        });
    }

    public function printableName()
    {
        if ($this->plainStatus() == 'removed') {
            return __('texts.user.removed_user');
        }

        $ret = $this->lastname . ' ' . $this->firstname;

        if (empty(trim($ret))) {
            $ret = $this->username;
        }

        return $ret;
    }

    public function getNameAttribute()
    {
        return $this->printableName();
    }

    public function printableHeader()
    {
        $ret = $this->printableName();

        if ($this->isFriend() === false) {
            $ret .= $this->headerIcons();
        }

        return $ret;
    }

    public function getShowURL()
    {
        return route('users.show', $this->id);
    }

    public function printableFriendHeader($aggregate)
    {
        $ret = $this->printableName();

        $tot = 0;
        foreach ($aggregate->orders as $order) {
            $order->setRelation('aggregate', $aggregate);
            $tot += $order->userBooking($this)->getValue('effective', false);
        }

        if ($tot != 0) {
            $ret .= '<div class="pull-right">' . __('texts.user.booking_friend_header', ['amount' => printablePriceCurrency($tot)]) . '</div>';
        }

        return $ret;
    }

    public function testUserAccess($myself = null): bool
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
        if (empty($this->picture)) {
            return '';
        }
        else {
            return url('users/picture/' . $this->id);
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function initialWelcome()
    {
        $this->load('contacts');

        if (! empty($this->getContactsByType('email'))) {
            $this->enforce_password_change = true;
            $this->access_token = Str::random(10);
            $this->save();

            try {
                $this->notify(new ManualWelcomeMessage($this->access_token));
            }
            catch (\Exception $e) {
                \Log::error('Impossibile inviare email di benvenuto a utente ' . $this->id . ': ' . $e->getMessage());
            }
        }
    }

    /*
        Genera la notifica relativa ad altre prenotazioni (oltre a quella
        dell'aggregato specificato) che devono essere ritirate dall'utente nel
        corso della giornata. Viene aggiunta nel pannello delle consegne e nel
        Dettaglio Consegne PDF
    */
    public function morePendingBookings($aggregate)
    {
        $other_bookings = $this->bookings()->where('status', 'pending')->whereHas('order', function ($query) use ($aggregate) {
            $query->where('aggregate_id', '!=', $aggregate->id)->where('shipping', $aggregate->shipping);
        })->get();

        if ($other_bookings->isEmpty() === false) {
            $notice = __('texts.user.pending_deliveries');
            $notice .= '<ul>';

            foreach ($other_bookings as $ob) {
                $notice .= '<li>' . $ob->order->printableName() . '</li>';
            }

            $notice .= '</ul>';

            return $notice;
        }

        return null;
    }

    public function anonymizeUserData()
    {
        /*
            Deliberatamente non vengono rimosse informazioni sul credito, in
            quanto ci si aspetta un esplicito movimento contabile di
            restituzione del credito rimanente all'utente
        */

        $this->contacts()->each(fn ($contact) => $contact->delete());

        if ($this->picture) {
            $picture = gas_storage_path($this->picture);
            \File::exists($picture) ?? \File::delete($picture);
        }

        $this->forceFill([
            'firstname' => __('texts.user.removed_user'),
            'lastname' => '',
            'suspended_at' => now(),
            'birthday' => '1900-01-01',
            'birthplace' => '',
            'picture' => '',
            'card_number' => '',
            'username' => Str::random(20),
        ])->save();
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        return $this->username;
    }

    /************************************************************** InCircles */

    public function eligibleGroups()
    {
        if ($this->isFriend()) {
            return new Collection();
        }
        else {
            $currentuser = Auth::user();
            if ($currentuser->can('users.admin', $currentuser->gas)) {
                return Group::where('context', 'user')->orderBy('name', 'asc')->get();
            }
            else {
                return Group::where('context', 'user')->where('user_selectable', true)->orderBy('name', 'asc')->get();
            }
        }
    }
}
