<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Events\SluggableCreating;
use App\AttachableTrait;
use App\GASModel;
use App\SluggableID;

class Gas extends Model
{
    use AttachableTrait, CreditableTrait, PayableTrait, GASModel, SluggableID;

    public $incrementing = false;

    protected $events = [
        'creating' => SluggableCreating::class,
    ];

    public static function commonClassName()
    {
        return 'GAS';
    }

    public function getLogoUrlAttribute()
    {
        if (empty($this->logo))
            return '';
        else
            return url('gas/' . $this->id . '/logo');
    }

    public function configs()
    {
        return $this->hasMany('App\Config');
    }

    public function getConfig($name)
    {
        foreach ($this->configs as $conf) {
            if ($conf->name == $name) {
                return $conf->value;
            }
        }

        return '';
    }

    public function setConfig($name, $value)
    {
        foreach ($this->configs as $conf) {
            if ($conf->name == $name) {
                $conf->value = $value;
                $conf->save();

                return;
            }
        }

        $conf = new Config();
        $conf->name = $name;
        $conf->value = $value;
        $conf->gas_id = $this->id;
        $conf->save();
    }

    private function mailConfig()
    {
        $conf = $this->getConfig('mail_conf');
        if ($conf == '') {
            return (object) [
                'driver' => '',
                'username' => '',
                'password' => '',
                'host' => '',
                'port' => '',
                'address' => '',
                'encryption' => '',
            ];
        } else {
            return json_decode($conf);
        }
    }

    public function has_mail()
    {
        return !empty($this->mailConfig()->host);
    }

    public function getMaildriverAttribute()
    {
        $config = $this->mailConfig();
        return $config->driver ?? 'smtp';
    }

    public function getMailusernameAttribute()
    {
        return $this->mailConfig()->username;
    }

    public function getMailpasswordAttribute()
    {
        return $this->mailConfig()->password;
    }

    public function getMailserverAttribute()
    {
        return $this->mailConfig()->host;
    }

    public function getMailportAttribute()
    {
        return $this->mailConfig()->port;
    }

    public function getMailaddressAttribute()
    {
        return $this->mailConfig()->address;
    }

    public function getMailsslAttribute()
    {
        return $this->mailConfig()->encryption;
    }

    public function getIbanAttribute()
    {
        return $this->getConfig('iban');
    }

    public function getFastShippingEnabledAttribute()
    {
        return $this->getConfig('fast_shipping_enabled') == '1';
    }

    public function getRestrictedAttribute()
    {
        return $this->getConfig('restricted') == '1';
    }

    /******************************************************** AttachableTrait */

    protected function requiredAttachmentPermission()
    {
        return 'gas.config';
    }

    /******************************************************** CreditableTrait */

    public static function balanceFields()
    {
        return [
            'bank' => 'Conto Corrente',
            'cash' => 'Cassa Contanti',
            'gas' => 'GAS',
            'suppliers' => 'Fornitori',
            'deposits' => 'Cauzioni',
        ];
    }
}
