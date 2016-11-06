<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Gas;
use App\User;
use App\Permission;
use App\Category;
use App\Measure;
use App\Notification;

class DatabaseSeeder extends Seeder
{
	public function run()
	{
		Model::unguard();

		DB::table('users')->delete();
		DB::table('password_resets')->delete();
		DB::table('configs')->delete();
		DB::table('gas')->delete();
		DB::table('suppliers')->delete();
		DB::table('products')->delete();
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

		$gas = Gas::create([
			'id' => str_slug('Senza Nome'),
			'name' => 'Senza Nome',
			'bank_balance' => 0,
			'cash_balance' => 0,
			'suppliers_balance' => 0,
			'deposit_balance' => 0
		]);

		$admin = User::create([
			'id' => str_slug('Amministratore Globale'),
			'gas_id' => $gas->id,
			'member_since' => date('Y-m-d', time()),
			'username' => 'root',
			'firstname' => 'Amministratore',
			'lastname' => 'Globale',
			'email' => 'admin@example.com',
			'password' => Hash::make('root')
		]);

		$permissions = Permission::allPermissions();
		foreach($permissions['App\Gas'] as $action => $desc) {
			$perm = new Permission(['user_id' => $admin->id, 'action' => $action]);
			$gas->permissions()->save($perm);
		}

		$categories = ['Non Specificato', 'Frutta', 'Verdura', 'Cosmesi', 'Bevande'];
		foreach ($categories as $cat) {
			Category::create([
				'id' => str_slug($cat),
				'name' => $cat
			]);
		}

		$measures = ['Non Specificato', 'Chili', 'Litri', 'Pezzi'];
		foreach ($measures as $name) {
			Measure::create([
				'id' => str_slug($name),
				'name' => $name
			]);
		}

		$notification = Notification::create([
			'creator_id' => $admin->id,
			'content' => 'Benvenuto in GASdotto!',
			'mailed' => false,
			'start_date' => date('Y-m-d'),
			'end_date' => date('Y-m-d', strtotime('+1 day')),
		]);

		$notification->users()->attach($admin->id, ['done' => false]);

		Model::reguard();
	}
}
