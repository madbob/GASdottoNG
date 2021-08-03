<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\Notifications\GenericNotificationWrapper;

use Auth;
use Mail;
use Log;

use App\Scopes\RestrictedGAS;

class Notification extends Model
{
    use GASModel, AttachableTrait;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new RestrictedGAS('users'));
    }

    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('done');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
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

        foreach ($this->users as $user) {
            try {
                $user->notify(new GenericNotificationWrapper($this));
            }
            catch(\Exception $e) {
                Log::error('Impossibile inoltrare mail di notifica a utente ' . $user->id . ': ' . $e->getMessage());
            }
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
