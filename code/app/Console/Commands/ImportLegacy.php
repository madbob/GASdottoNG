<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

use DB;
use App;
use Hash;
use App\User;
use App\Gas;
use App\Role;
use App\Balance;
use App\Delivery;
use App\Supplier;
use App\Contact;
use App\Category;
use App\Measure;
use App\Product;
use App\VatRate;
use App\Variant;
use App\VariantValue;

class ImportLegacy extends Command
{
    protected $signature = 'import:legacy {old_driver} {old_host} {old_username} {old_password} {old_database} {new_driver} {new_host} {new_username} {new_password} {new_database}';
    protected $description = 'Importa dati da una istanza di GASdotto Legacy';

    public function __construct()
    {
        parent::__construct();
    }

    private function addressTranslate($old)
    {
        list($street, $cap, $city) = explode(';', $old);
        list($prefix, $street) = explode(':', $street);
        list($prefix, $cap) = explode(':', $cap);
        list($prefix, $city) = explode(':', $city);
        return sprintf('%s, %s, %s', $street, $cap, $city);
    }

    private function handleContact($type, $external_name, $source, $obj)
    {
        if (empty($source->$external_name))
            return;

        $c = new Contact();
        $c->type = $type;

        if ($type == 'address')
            $c->value = $this->addressTranslate($source->$external_name);
        else
            $c->value = $source->$external_name;

        $c->target_id = $obj->id;
        $c->target_type = get_class($obj);

        $c->save();
    }

    public function handle()
    {
        Model::unguard();

        $factory = App::make('db.factory');

        $old_config = [
            'driver' => $this->argument('old_driver'),
            'host' => $this->argument('old_host'),
            'username' => $this->argument('old_username'),
            'password' => $this->argument('old_password'),
            'database' => $this->argument('old_database'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ];
        $old = $factory->make($old_config);

        $new_config = [
            'driver' => $this->argument('new_driver'),
            'host' => $this->argument('new_host'),
            'username' => $this->argument('new_username'),
            'password' => $this->argument('new_password'),
            'database' => $this->argument('new_database'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ];
        $capsule = new Capsule();
        $capsule->addConnection($new_config);
        $capsule->bootEloquent();

        DB::table('users')->delete();
        DB::table('password_resets')->delete();
        DB::table('gas')->delete();
        DB::table('products')->delete();
        DB::table('suppliers')->delete();
        DB::table('orders')->delete();
        DB::table('aggregates')->delete();
        DB::table('variants')->delete();
        DB::table('variant_values')->delete();
        DB::table('categories')->delete();
        DB::table('measures')->delete();
        DB::table('deliveries')->delete();
        DB::table('notifications')->delete();
        DB::table('bookings')->delete();
        DB::table('booked_products')->delete();
        DB::table('booked_product_variants')->delete();
        DB::table('movements')->delete();
        DB::table('contacts')->delete();
        DB::table('comments')->delete();

        $map = [];

        $user_role = Role::create([
            'name' => 'Utente',
            'always' => true,
            'actions' => 'users.view,supplier.book'
        ]);

        $admin_role = Role::create([
            'name' => 'Amministratore',
            'actions' => 'gas.access,gas.permissions,gas.config,supplier.add,users.admin,movements.admin,categories.admin,measures.admin,gas.statistics,notifications.admin'
        ]);

        $referrer_role = Role::create([
            'name' => 'Referente',
            'actions' => 'supplier.modify,supplier.orders,supplier.shippings'
        ]);

        /*
            Al momento ignoro il caso di importazione di una
            installazione multi-GAS, mai realmente utilizzata,
            assumendo che tutti gli elementi dipendano dal primo GAS
            che si trova. Potrebbe essere necessario in futuro
            correggere questa assunzione
        */
        $master_gas = null;

        $map['gas'] = [];
        $query = 'SELECT * FROM GAS';
        $result = $old->select($query);

        foreach ($result as $row) {
            $obj = new Gas();
            $obj->name = $row->name;
            $obj->email = $row->mail;
            $obj->save();
            $map['gas'][$row->id] = $obj->id;

            $master_gas = $obj;

            $balance = new Balance();
            $balance->gas_id = $obj->id;
            $balance->date = date('Y-m-d G:i:s');
            $balance->bank = $row->current_bank_balance;
            $balance->cash = $row->current_cash_balance;
            $balance->suppliers = $row->current_orders_balance;
            $balance->deposits = $row->current_deposit_balance;
            $balance->save();

            sleep(1);

            $balance = $balance->replicate();
            $balance->date = date('Y-m-d G:i:s');
            $balance->save();
        }

        $map['deliveries'] = [];
        $query = 'SELECT * FROM Shippingplace';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                $obj = new Delivery();
                $obj->name = $row->name;
                $obj->address = $this->addressTranslate($row->address);
                $obj->default = $row->is_default;
                $obj->save();
                $map['deliveries'][$row->id] = $obj->id;
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'importazione del luogo di consegna %s: %s\n", $row->name, $e->getMessage());
            }
        }

        $map['users'] = [];
        $query = 'SELECT * FROM Users';
        $result = $old->select($query);

        foreach ($result as $row) {
            if ($row->login == '') {
                continue;
            }

            try {
                $obj = new User();
                $obj->gas_id = $master_gas->id;
                $obj->username = $row->login;
                $obj->firstname = $row->firstname;
                $obj->lastname = $row->surname;
                $obj->password = Hash::make($row->login);
                $obj->birthday = $row->birthday;
                $obj->family_members = $row->family;
                $obj->taxcode = $row->codfisc;
                $obj->member_since = $row->join_date;
                $obj->card_number = $row->card_number;
                $obj->last_login = $row->lastlogin;
                $obj->iban = $row->bank_account;
                $obj->sepa_subscribe = $row->sepa_subscribe;
                $obj->sepa_first = $row->first_sepa;

                if (array_key_exists($row->shipping, $map['deliveries'])) {
                    $obj->preferred_delivery_id = $map['deliveries'][$row->shipping];
                }

                $obj->save();

                $this->handleContact('phone', 'phone', $row, $obj);
                $this->handleContact('email', 'mail', $row, $obj);
                $this->handleContact('email', 'mail2', $row, $obj);
                $this->handleContact('address', 'address', $row, $obj);

                $map['users'][$row->id] = $obj->id;

                if ($row->privileges == 2) {
                    $obj->addRole($admin_role, $master_gas);
                }
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'importazione dell'utente %s: %s\n", $row->login, $e->getMessage());
            }
        }

        $map['suppliers'] = [];
        $query = 'SELECT * FROM Supplier';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                $obj = new Supplier();
                $obj->name = $row->name;
                $obj->business_name = $row->name;
                $obj->description = $row->description;
                $obj->taxcode = $row->tax_code;
                $obj->vat = $row->vat_number;
                $obj->save();

                $this->handleContact('address', 'address', $row, $obj);
                $this->handleContact('phone', 'phone', $row, $obj);
                $this->handleContact('email', 'mail', $row, $obj);
                $this->handleContact('fax', 'fax', $row, $obj);
                $this->handleContact('website', 'website', $row, $obj);

                $map['suppliers'][$row->id] = $obj->id;

                if ($row->hidden)
                    $obj->delete();
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'importazione del fornitore %s: %s\n", $row->name, $e->getMessage());
            }
        }

        $query = 'SELECT * FROM Supplier_references';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                $parent = Supplier::withTrashed()->findOrFail($map['suppliers'][$row->parent]);
                $target = User::findOrFail($map['users'][$row->target]);
                $target->addRole($referrer_role, $parent);
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'assegnazione di privilegi: %s\n", $e->getMessage());
            }
        }

        $map['categories'] = [];
        $query = 'SELECT * FROM Category';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                $obj = new Category();
                $obj->name = $row->name;
                $obj->save();
                $map['categories'][$row->id] = $obj->id;
            }
            catch (\Exception $e) {
                $obj = Category::where('name', '=', $row->name)->first();
                if ($obj != null) {
                    $map['categories'][$row->id] = $obj->id;
                } else {
                    echo sprintf("Errore nell'importazione della categoria %s: %s\n", $row->name, $e->getMessage());
                }
            }
        }

        $map['measures'] = [];
        $query = 'SELECT * FROM Measure';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                $obj = new Measure();
                $obj->name = $row->name;
                $obj->save();
                $map['measures'][$row->id] = $obj->id;
            }
            catch (\Exception $e) {
                $obj = Measure::where('name', '=', $row->name)->first();
                if ($obj != null) {
                    $map['measures'][$row->id] = $obj->id;
                } else {
                    echo sprintf("Errore nell'importazione della misura %s: %s\n", $row->name, $e->getMessage());
                }
            }
        }

        $map['products'] = [];
        $query = 'SELECT * FROM Product WHERE archived = false';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                $obj = new Product();
                $obj->name = $row->name;
                $obj->supplier_id = $map['suppliers'][$row->supplier];
                $obj->supplier_code = $row->code;
                $obj->category_id = $map['categories'][$row->category];
                $obj->measure_id = $map['measures'][$row->measure];
                $obj->active = $row->available;
                $obj->description = $row->description;
                $obj->price = $row->unit_price;
                $obj->transport = $row->shipping_price;
                $obj->variable = $row->mutable_price;
                $obj->portion_quantity = $row->unit_size;
                $obj->package_size = $row->stock_size;
                $obj->min_quantity = $row->minimum_order;
                $obj->multiple = $row->multiple_order;
                $obj->max_available = $row->total_max_order;
                $obj->save();
                $map['products'][$row->id] = $obj->id;
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'importazione del prodotto %s: %s\n", $row->name, $e->getMessage());
            }
        }

        foreach ($map['products'] as $original_id => $new_id) {
            $query = "SELECT * FROM Product_variants WHERE parent = $original_id";
            $result = $old->select($query);

            foreach ($result as $row) {
                try {
                    $query = 'SELECT * FROM Productvariant WHERE id = '.$row->target;
                    $original_variant = $old->select($query);

                    $obj = new Variant();
                    $obj->name = $original_variant[0]->name;
                    $obj->product_id = $new_id;
                    $obj->save();

                    $query = "SELECT * FROM Productvariant_values WHERE parent = $row->target";
                    $original_values = $old->select($query);
                    foreach ($original_values as $value_row) {
                        try {
                            $query = 'SELECT * FROM Productvariantvalue WHERE id = '.$value_row->target;
                            $original_value = $old->select($query);

                            $value_obj = new VariantValue();
                            $value_obj->variant_id = $obj->id;
                            $value_obj->value = $original_value[0]->name;
                            $value_obj->save();
                        }
                        catch (\Exception $e) {
                            echo sprintf("Errore nell'importazione valore variante %s: %s\n", $original_variant[0]->name, $e->getMessage());
                        }
                    }
                }
                catch (\Exception $e) {
                    echo sprintf("Errore nell'importazione variante %s: %s\n", $original_variant[0]->name, $e->getMessage());
                }
            }
        }

        VatRate::create([
            'name' => '4%',
            'percentage' => 4,
        ]);

        VatRate::create([
            'name' => '10%',
            'percentage' => 10,
        ]);

        VatRate::create([
            'name' => '22%',
            'percentage' => 22,
        ]);

        Model::reguard();
    }
}
