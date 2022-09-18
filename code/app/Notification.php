<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

use Auth;
use Mail;
use Log;

use App\Jobs\DeliverNotification;
use App\Scopes\RestrictedGAS;

class Notification extends Model
{
    use HasFactory, GASModel, AttachableTrait;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new RestrictedGAS());
    }

    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('done');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function gas()
    {
        return $this->belongsTo('App\Gas');
    }

    public function hasUser($user)
    {
        foreach ($this->users as $u) {
            if ($u->id == $user->id) {
                return true;
            }
        }

        return false;
    }

    public function sendMail()
    {
        if ($this->mailed == false) {
            return;
        }

        try {
            DeliverNotification::dispatch($this->id);
        }
        catch(\Exception $e) {
            Log::error('Unable to trigger DeliverNotification job while sending notification: ' . $e->getMessage());
        }
    }

    public function printableName()
    {
        $users = $this->users;
        $c = $users->count();

        if ($c == 1) {
            return $users->first()->printableName();
        } else {
            return sprintf('%d utenti', $c);
        }
    }

    public function printableHeader()
    {
        return $this->printableDate('start_date') . ' - ' . $this->printableName() . ' - ' . substr(strip_tags($this->content), 0, 100) . '...';
    }
}
