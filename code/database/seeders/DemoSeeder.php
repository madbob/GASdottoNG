<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Hash;

use App\Gas;
use App\User;
use App\Role;
use App\Supplier;
use App\Product;
use App\Measure;
use App\Category;
use App\VatRate;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $gas = Gas::where('name', '!=', '')->first();

        $gas->name = 'GAS Demo';
        $gas->message = "Questa istanza permette di avere una idea del funzionamento di GASdottoNG.\n\nPer accedere:\nUtente amministratore: username: root, password: root\nUtente non privilegiato: username: user, password: user\n\nL'inoltro di messaggi email da questa istanza è deliberatamente disabilitato, per evitare abusi.\n\nQuesta istanza viene quotidianamente rinnovata con le ultimissime modifiche (al contrario delle istanze hostate su gasdotto.net, sulle quali viene condotto qualche test in più prima della pubblicazione). GASdottoNG è un progetto in continua evoluzione: se noti qualcosa che non va, o una funzione che manca, mandaci una mail a info@madbob.org";
        $gas->save();

        User::create([
            'id' => str_slug('Utente Normale'),
            'gas_id' => $gas->id,
            'member_since' => date('Y-m-d', time()),
            'username' => 'user',
            'firstname' => 'Mario',
            'lastname' => 'Rossi',
            'password' => Hash::make('user'),
        ]);

        $referrer_role = Role::where('name', 'Referente')->first();
        $administrator = User::where('username', 'root')->first();

        $data = [
            'Fornitore delle Mele' => [
                'Mele Rosse',
                'Mele Gialle',
                'Mele Verdi',
            ],
            'Fornitore delle Arance' => [
                'Arance',
                'Mandarini',
                'Clementine',
            ],
        ];

        $measure = Measure::where('name', '=', 'Chili')->first();
        $category = Category::where('name', '=', 'Frutta')->first();
        $vat_rate = VatRate::where('percentage', '=', '22')->first();

        foreach ($data as $s_name => $products) {
            $s = Supplier::create([
                'id' => str_slug($s_name),
                'name' => $s_name,
                'order_method' => '',
                'payment_method' => ''
            ]);

            $gas->suppliers()->attach($s->id);

            foreach ($products as $p_name) {
                Product::create([
                    'id' => str_slug($p_name),
                    'name' => $p_name,
                    'supplier_id' => $s->id,
                    'active' => true,
                    'price' => rand(200, 500) / 100,
                    'measure_id' => $measure->id,
                    'category_id' => $category->id,
                    'vat_rate_id' => $vat_rate->id
                ]);
            }

            $administrator->addRole($referrer_role, $s);
        }
    }
}
