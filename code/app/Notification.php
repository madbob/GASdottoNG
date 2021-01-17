<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\Notifications\GenericNotificationWrapper;

use Auth;
use Mail;
use Log;

use App\GASModel;

class Notification extends Model
{
    use GASModel, AttachableTrait;

    protected static function boot()
    {
        parent::boot();

        $user = Auth::user();
        if ($user != null) {
            $gas_id = $user->gas->id;

            static::addGlobalScope('gas', function (Builder $builder) use ($gas_id) {
                $builder->whereHas('users', function($query) use ($gas_id) {
                    $query->where('gas_id', $gas_id);
                });
            });
        }
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

                /*
                    Onde evitare di farsi bloccare dal server SMTP, qui attendiamo
                    un pochino tra una mail e l'altra
                */
                usleep(200000);
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
