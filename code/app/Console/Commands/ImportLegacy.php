<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Str;

use DB;
use App;
use Hash;
use App\User;
use App\Gas;
use App\Role;
use App\Balance;
use App\Delivery;
use App\Supplier;
use App\Attachment;
use App\Contact;
use App\Category;
use App\Measure;
use App\Product;
use App\VatRate;
use App\Movement;
use App\Variant;
use App\VariantValue;

class ImportLegacy extends Command
{
    protected $signature = 'import:legacy {old_path} {old_driver} {old_host} {old_username} {old_password} {old_database}';
    protected $description = 'Importa dati da una istanza di GASdotto Legacy';

    private $last_balance_date = null;

    public function __construct()
    {
        parent::__construct();
    }

    private function addressTranslate($old)
    {
        list($street, $cap, $city) = explode(';', $old);
        list($prefix, $street) = explode(':', $street);
        $street = str_replace(',', '', $street);
        list($prefix, $cap) = explode(':', $cap);
        $cap = str_replace(',', '', $cap);
        list($prefix, $city) = explode(':', $city);
        $city = str_replace(',', '', $city);
        return sprintf('%s, %s, %s', $street, $city, $cap);
    }

    private function handleContact($type, $external_name, $source, $obj)
    {
        if (empty($source->$external_name))
            return;

        $c = new Contact();
        $c->type = $type;

        if ($type == 'address') {
            $test = $this->addressTranslate($source->$external_name);
            $test = str_replace(' ', '', $test);
            $test = str_replace(',', '', $test);
            if (empty($test))
                return;

            $c->value = $test;
        }
        else {
            $c->value = $source->$external_name;
        }

        $c->target_id = $obj->id;
        $c->target_type = get_class($obj);

        $c->save();
    }

    private function appendBalance($obj, $row, $full)
    {
        if (is_null($this->last_balance_date)) {
            if (isset($row->last_balance_date)) {
                $this->last_balance_date = $row->last_balance_date;
            }
            else {
                $this->last_balance_date = date('Y-m-d');
            }
        }

        $balance = new Balance();
        $balance->target_id = $obj->id;
        $balance->target_type = get_class($obj);
        $balance->date = $this->last_balance_date;

        if ($full) {
            $balance->bank = $row->last_bank_balance ?? 0;
            $balance->cash = $row->last_cash_balance ?? 0;
            $balance->gas = 0;
            $balance->suppliers = $row->last_orders_balance ?? 0;
            $balance->deposits = $row->last_deposit_balance ?? 0;
        }
        else {
            $balance->bank = $row->last_balance ?? 0;
        }

        $balance->save();

        for($i = 0; $i < 2; $i++) {
            $balance = new Balance();
            $balance->target_id = $obj->id;
            $balance->target_type = get_class($obj);
            $balance->date = date('Y-m-d G:i:s', time() + 1);
            $balance->current = true;

            if ($full) {
                $balance->bank = $row->current_bank_balance ?? 0;
                $balance->cash = $row->current_cash_balance ?? 0;
                $balance->gas = 0;
                $balance->suppliers = $row->current_orders_balance ?? 0;
                $balance->deposits = $row->current_deposit_balance ?? 0;
            }
            else {
                $balance->bank = $row->current_balance ?? 0;
            }

            $balance->save();
        }
    }

    private function importFiles($old_path, $old_db, $type, $new_ids)
    {
        if ($type == 'supplier')
            $table = 'Supplier_files';
        else
            $table = 'Gas_files';

        try {
            $query = 'SELECT * FROM ' . $table;
            $result = $old_db->select($query);
        }
        catch(\Exception $e) {
            return;
        }

        foreach ($result as $row) {
            $subrow = null;

            try {
                $old_id = $row->target;
                $query = 'SELECT * FROM Customfile WHERE id = ' . $old_id;
                $subresult = $old_db->select($query);

                foreach ($subresult as $subrow) {
                    if (empty($subrow->server_path)) {
                        continue;
                    }

                    $old_file_path = sprintf('%s%s', $old_path, $subrow->server_path);
                    if (file_exists($old_file_path) == false) {
                        echo sprintf("File non reperibile: %s\n", $old_file_path);
                    }
                    else {
                        if ($type == 'supplier')
                            $parent = Supplier::withTrashed()->findOrFail($new_ids['suppliers'][$row->parent]);
                        else
                            $parent = Gas::findOrFail($new_ids['gas'][$row->parent]);

                        $filename = basename($subrow->server_path);
                        $new_path = sprintf('%s/%s', $parent->filesPath(), $filename);
                        copy($old_file_path, $new_path);

                        $file = new Attachment();
                        $file->target_type = get_class($parent);
                        $file->target_id = $parent->id;
                        $file->name = $subrow->name;
                        $file->filename = $filename;
                        $file->save();
                    }
                }
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'importazione del file %s: %s\n", ($subrow->name ?? '???'), $e->getMessage());
            }
        }
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
            'driver' => env('DB_CONNECTION'),
            'host' => env('DB_HOST'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ];
        $capsule = new Capsule();
        $capsule->addConnection($new_config);
        $capsule->bootEloquent();

        DB::table('attached_role_user')->delete();
        DB::table('role_user')->delete();
        DB::table('roles')->delete();
        DB::table('users')->delete();
        DB::table('password_resets')->delete();
        DB::table('attachments')->delete();
        DB::table('balances')->delete();
        DB::table('configs')->delete();
        DB::table('gas')->delete();
        DB::table('vat_rates')->delete();
        DB::table('products')->delete();
        DB::table('suppliers')->delete();
        DB::table('order_product')->delete();
        DB::table('orders')->delete();
        DB::table('aggregates')->delete();
        DB::table('variant_values')->delete();
        DB::table('variants')->delete();
        DB::table('categories')->delete();
        DB::table('measures')->delete();
        DB::table('deliveries')->delete();
        DB::table('notification_user')->delete();
        DB::table('notifications')->delete();
        DB::table('booked_product_components')->delete();
        DB::table('booked_product_variants')->delete();
        DB::table('bookings')->delete();
        DB::table('booked_products')->delete();
        DB::table('booked_product_variants')->delete();
        DB::table('movements')->delete();
        DB::table('contacts')->delete();
        DB::table('comments')->delete();

        $old_path = $this->argument('old_path');

        $map = [];

        $admin_role = Role::create([
            'name' => 'Amministratore',
            'actions' => 'gas.access,gas.permissions,gas.config,supplier.add,users.admin,users.movements,movements.admin,movements.types,categories.admin,measures.admin,gas.statistics,notifications.admin'
        ]);

        $user_role = Role::create([
            'name' => 'Utente',
            'always' => true,
            'actions' => 'users.view,supplier.book',
            'parent_id' => $admin_role->id
        ]);

        $referrer_role = Role::create([
            'name' => 'Referente',
            'actions' => 'supplier.modify,supplier.orders,supplier.shippings',
            'parent_id' => $admin_role->id
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
        $query = 'SELECT * FROM GAS ORDER BY id ASC';
        $result = $old->select($query);
        $existing_gas = [];

        foreach ($result as $row) {
            if (in_array($row->name, $existing_gas)) {
                continue;
            }

            $obj = new Gas();
            $obj->name = $row->name;
            $obj->email = $row->mail;
            $obj->save();
            $map['gas'][$row->id] = $obj->id;

            $master_gas = $obj;
            $this->appendBalance($obj, $row, true);

            if (isset($row->payment_date))
                $obj->setConfig('year_closing', $row->payment_date);
            if (isset($row->default_fee))
                $obj->setConfig('annual_fee_amount', $row->default_fee);
            if (isset($row->default_deposit))
                $obj->setConfig('deposit_amount', $row->default_deposit);

            $existing_gas[] = $row->name;
        }

        $map['deliveries'] = [];
        $query = 'SELECT * FROM Shippingplace';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                if ($row->name == 'Nuovo Luogo di Consegna')
                    continue;

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
                $obj->taxcode = $row->codfisc ?? '';
                $obj->member_since = $row->join_date;
                $obj->card_number = $row->card_number;
                $obj->last_login = $row->lastlogin;

                $rid_info = [
                    'iban' => $row->bank_account ? $row->bank_account : '',
                    'id' => '',
                    'date' => ''
                ];
                $obj->rid = $rid_info;

                if (array_key_exists($row->shipping, $map['deliveries'])) {
                    $obj->preferred_delivery_id = $map['deliveries'][$row->shipping];
                }

                if (!empty($row->photo)) {
                    $old_file_path = sprintf('%s/%s', $old_path, $row->photo);
                    if (file_exists($old_file_path) == false) {
                        echo sprintf("File non reperibile: %s\n", $old_file_path);
                    }
                    else {
                        $filename = Str::random(30);
                        $obj->picture = sprintf('app/%s', $filename);
                        copy($old_file_path, gas_storage_path($obj->picture));
                    }
                }

                $obj->save();

                $this->handleContact('phone', 'phone', $row, $obj);
                $this->handleContact('mobile', 'mobile', $row, $obj);
                $this->handleContact('email', 'mail', $row, $obj);
                $this->handleContact('email', 'mail2', $row, $obj);
                $this->handleContact('address', 'address', $row, $obj);

                $this->appendBalance($obj, $row, false);

                $map['users'][$row->id] = $obj->id;

                if ($row->privileges == 2) {
                    $obj->addRole($admin_role, $master_gas);
                }
                else if ($row->privileges == 3) {
                    $obj->deleted_at = $row->leaving_date;
                    $obj->save();
                }
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'importazione dell'utente %s: %s\n", $row->login, $e->getMessage());
            }
        }

        foreach ($result as $row) {
            try {
                $fee_id = null;
                $deposit_id = null;

                if (!empty($row->paying)) {
                    $query = 'SELECT * FROM BankMovement WHERE id = ' . $row->paying;
                    $inner_result = $old->select($query)[0];
                    if ($inner_result->amount != 0) {
                        $movement = new Movement();
                        $movement->type = 'annual-fee';
                        $movement->method = $inner_result->method == 0 ? 'bank' : 'cash';
                        $movement->sender_id = $map['users'][$row->id];
                        $movement->sender_type = 'App\User';
                        $movement->target_id = $master_gas->id;
                        $movement->target_type = get_class($master_gas);
                        $movement->amount = $inner_result->amount;
                        $movement->date = $inner_result->date;
                        $movement->registration_date = $inner_result->registrationdate;
                        $movement->registerer_id = $map['users'][$inner_result->registrationperson];
                        $movement->archived = true;
                        $movement->save();
                        $fee_id = $movement->id;
                    }
                }

                if (!empty($row->deposit)) {
                    $query = 'SELECT * FROM BankMovement WHERE id = ' . $row->deposit;
                    $inner_result = $old->select($query)[0];
                    if ($inner_result->amount != 0) {
                        $movement = new Movement();
                        $movement->type = 'deposit-pay';
                        $movement->method = $inner_result->method == 0 ? 'bank' : 'cash';
                        $movement->sender_id = $map['users'][$row->id];
                        $movement->sender_type = 'App\User';
                        $movement->target_id = $master_gas->id;
                        $movement->target_type = get_class($master_gas);
                        $movement->amount = $inner_result->amount;
                        $movement->date = $inner_result->date;
                        $movement->registration_date = $inner_result->registrationdate;
                        $movement->registerer_id = $map['users'][$inner_result->registrationperson];
                        $movement->archived = true;
                        $movement->save();
                        $deposit_id = $movement->id;
                    }
                }

                if ($fee_id != null || $deposit_id != null) {
                    $obj = User::withTrashed()->find($map['users'][$row->id]);
                    $obj->fee_id = $fee_id;
                    $obj->deposit_id = $deposit_id;
                    $obj->save();
                }
            }
            catch(\Exception $e) {
                echo sprintf("Errore nell'importazione quota iscrizione e cauzione di %s: %s\n", $row->login, $e->getMessage());
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
                $obj->order_method = $row->order_mode;
                $obj->payment_method = $row->paying_mode;
                $obj->save();

                $master_gas->suppliers()->attach($obj->id);

                $this->handleContact('address', 'address', $row, $obj);
                $this->handleContact('phone', 'phone', $row, $obj);
                $this->handleContact('email', 'mail', $row, $obj);
                $this->handleContact('fax', 'fax', $row, $obj);
                $this->handleContact('website', 'website', $row, $obj);

                $this->appendBalance($obj, $row, false);

                $map['suppliers'][$row->id] = $obj->id;

                if ($row->hidden)
                    $obj->delete();
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'importazione del fornitore %s: %s\n", $row->name, $e->getMessage());
            }
        }

        $this->importFiles($old_path, $old, 'supplier', $map);
        $this->importFiles($old_path, $old, 'gas', $map);

        $query = 'SELECT * FROM Supplier_references';
        $result = $old->select($query);

        foreach ($result as $row) {
            try {
                $target = User::withTrashed()->findOrFail($map['users'][$row->target]);
                if($target != null) {
                    $parent = Supplier::withTrashed()->findOrFail($map['suppliers'][$row->parent]);
                    $target->addRole($referrer_role, $parent);
                }
            }
            catch (\Exception $e) {
                echo sprintf("Errore nell'assegnazione di privilegi: %s\n", $e->getMessage());
            }
        }

        $first_category = Category::create([
            'id' => 1,
            'name' => 'Non Specificato'
        ]);

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
                $obj->discrete = true;
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
                $obj->max_available = $row->total_max_order ?? 0;

                if (isset($map['categories'][$row->category]))
                    $obj->category_id = $map['categories'][$row->category];
                else
                    $obj->category_id = $first_category->id;

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
                    echo sprintf("Errore nell'importazione variante %s: %s\n", ($original_variant[0]->name ?? '???'), $e->getMessage());
                }
            }
        }

        VatRate::create([
            'name' => 'Minima',
            'percentage' => 4,
        ]);

        VatRate::create([
            'name' => 'Ridotta',
            'percentage' => 10,
        ]);

        VatRate::create([
            'name' => 'Ordinaria',
            'percentage' => 22,
        ]);

        Model::reguard();
    }
}
