<?php

use App\Balance;
use App\Category;
use App\Gas;
use App\Measure;
use App\Notification;
use App\User;
use App\Role;
use App\VatRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();
        DB::table('password_resets')->delete();
        DB::table('configs')->delete();
        DB::table('gas')->delete();
        DB::table('suppliers')->delete();
        DB::table('products')->delete();
        DB::table('orders')->delete();
        DB::table('aggregates')->delete();
        DB::table('variant_values')->delete();
        DB::table('variants')->delete();
        DB::table('categories')->delete();
        DB::table('measures')->delete();
        DB::table('deliveries')->delete();
        DB::table('notifications')->delete();
        DB::table('bookings')->delete();
        DB::table('booked_products')->delete();
        DB::table('booked_product_variants')->delete();
        DB::table('movement_types')->delete();
        DB::table('movements')->delete();
        DB::table('contacts')->delete();
        DB::table('comments')->delete();

        $gas = Gas::create([
            'id' => str_slug('Senza Nome'),
            'name' => 'Senza Nome',
        ]);

        $balance = Balance::create([
            'target_id' => $gas->id,
            'target_type' => get_class($gas),
            'bank' => 0,
            'cash' => 0,
            'suppliers' => 0,
            'deposits' => 0,
            'date' => date('Y-m-d', time())
        ]);

        $gas->setConfig('year_closing', date('Y') . '-09-01');
        $gas->setConfig('annual_fee_amount', 10.00);
        $gas->setConfig('deposit_amount', 10.00);

        $user_role = Role::create([
            'name' => 'Utente',
            'actions' => 'users.view,supplier.book',
            'always' => true
        ]);

        $admin_role = Role::create([
            'name' => 'Amministratore',
            'actions' => 'gas.access,gas.permissions,gas.config,supplier.add,users.admin,users.movements,movements.admin,movements.types,categories.admin,measures.admin,gas.statistics,notifications.admin'
        ]);

        $referrer_role = Role::create([
            'name' => 'Referente',
            'actions' => 'supplier.modify,supplier.orders,supplier.shippings'
        ]);

        $admin = User::create([
            'id' => str_slug('Amministratore Globale'),
            'gas_id' => $gas->id,
            'member_since' => date('Y-m-d', time()),
            'username' => 'root',
            'firstname' => 'Amministratore',
            'lastname' => 'Globale',
            'password' => Hash::make('root'),
        ]);

        $admin->addRole($user_role, $gas);
        $admin->addRole($admin_role, $gas);

        $categories = ['Non Specificato', 'Frutta', 'Verdura', 'Cosmesi', 'Bevande'];
        foreach ($categories as $cat) {
            Category::create([
                'id' => str_slug($cat),
                'name' => $cat,
            ]);
        }

        $measures = ['Non Specificato', 'Chili', 'Litri', 'Pezzi'];
        foreach ($measures as $name) {
            Measure::create([
                'id' => str_slug($name),
                'name' => $name,
            ]);
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

        $notification = Notification::create([
            'creator_id' => $admin->id,
            'content' => 'Benvenuto in GASdotto!',
            'mailed' => false,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
        ]);

        $notification->users()->attach($admin->id, ['done' => false]);

        $this->call(MovementTypesSeeder::class);
    }
}
