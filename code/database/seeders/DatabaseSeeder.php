<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use DB;
use Hash;

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
    private function resetAll()
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
    }

    private function balanceInit($gas)
    {
        Balance::create([
            'target_id' => $gas->id,
            'target_type' => get_class($gas),
            'bank' => 0,
            'cash' => 0,
            'suppliers' => 0,
            'deposits' => 0,
            'date' => date('Y-m-d', time())
        ]);
    }

    private function roleInit($gas)
    {
        $queue = systemParameters('Roles');

        while(true) {
            $next_queue = [];

            foreach ($queue as $identifier => $instance) {
                if (Role::where('identifier', $identifier)->count() == 0) {
                    try {
                        $instance->create();
                    }
                    catch(\Exception $e) {
                        $next_queue[$identifier] = $instance;
                    }
                }
            }

            if (empty($next_queue)) {
                break;
            }

            $queue = $next_queue;
        }

        $gas->setConfig('roles', (object) [
            'user' => roleByIdentifier('user')->id,
            'friend' => roleByIdentifier('friend')->id,
            'multigas' => roleByIdentifier('secondary_admin')->id,
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

        $admin->addRole(roleByIdentifier('user'), $gas);
        $admin->addRole(roleByIdentifier('admin'), $gas);

        return $admin;
    }

    private function categoryInit()
    {
        $categories = ['Non Specificato', 'Frutta', 'Verdura', 'Cosmesi', 'Bevande'];
        foreach ($categories as $cat) {
            Category::create([
                'id' => Str::slug($cat),
                'name' => $cat,
            ]);
        }
    }

    private function measureInit()
    {
        $measures = ['Non Specificato' => true, 'Chili' => false, 'Litri' => false, 'Pezzi' => true];
        foreach($measures as $name => $discrete) {
            Measure::create([
                'id' => Str::slug($name),
                'name' => $name,
                'discrete' => $discrete,
            ]);
        }
    }

    private function vatInit()
    {
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
    }

    private function initialNotification($admin)
    {
        $notification = Notification::create([
            'creator_id' => $admin->id,
            'content' => "Benvenuto in GASdotto!\n\nPer assistenza puoi rivolgerti alla mailing list degli utenti su https://groups.google.com/forum/#!forum/gasdotto-dev o all'indirizzo mail info@madbob.org",
            'mailed' => false,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+7 days')),
        ]);

        $notification->users()->attach($admin->id, ['done' => false]);
    }

    public function run()
    {
        $this->resetAll();

        $gas = Gas::create([
            'id' => env('DB_DATABASE'),
            'name' => 'Senza Nome',
        ]);

        $this->balanceInit($gas);

        $admin = $this->roleInit($gas);

        $this->categoryInit();
        $this->measureInit();
        $this->vatInit();
        $this->initialNotification($admin);

        $this->call(CurrenciesSeeder::class);
        $this->call(MovementTypesSeeder::class);
        $this->call(ModifierTypesSeeder::class);
    }
}
