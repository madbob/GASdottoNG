<?php

namespace app;

use Illuminate\Database\Eloquent\Model;
use Mail;
use App\GASModel;

class Notification extends Model
{
    use GASModel;

    public function users()
    {
        return $this->belongsToMany('App\User');
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
            Mail::send(['text' => 'emails.notification'], ['notification' => $this], function ($m) use ($user) {
                $m->to($user->email, $user->name)->subject('nuova notifica');
            });

            /*
                Onde evitare di farsi bloccare dal server SMTP,
                qui attendiamo mezzo secondo tra una mail e
                l'altra
            */
            usleep(500000);
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
        return $this->printableDate('start_date').' / '.$this->printableName();
    }
}
