<?php

use App\User;
use App\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	public function run()
	{
		Model::unguard();

		$this->call('GASdottoSeeder');
	}

}

class GASdottoSeeder extends Seeder {

	public function run()
	{
		DB::table('users')->delete();
		DB::table('suppliers')->delete();

		User::create([
			'name' => 'mario',
			'firstname' => 'Mario',
			'surname' => 'Rossi',
			'email' => 'mario@rossi.com',
			'password' => Hash::make('pippo')
		]);

		Supplier::create([
			'name' => 'ACME Inc.',
			'email' => 'info@acme.com',
			'phone' => '7894567458',
			'description' => 'Fornitore di trappole per Road Runners'
		]);

		Supplier::create([
			'name' => 'Mega Ditta',
			'email' => 'info@megaditta.it',
			'phone' => '78234465',
			'description' => 'Qui lavora Fantozzi'
		]);

		Supplier::create([
			'name' => 'Bio Ritmo',
			'email' => 'info@bioritmo.it',
			'phone' => '23657634435',
			'description' => 'Musica a chilometri zero'
		]);
	}

}
