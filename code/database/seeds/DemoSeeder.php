<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Gas;
use App\User;
use App\Supplier;
use App\Product;
use App\Measure;
use App\Category;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $gas = Gas::where('name', '!=', '')->first();

        $gas->name = 'GAS Demo';
        $gas->message = "Questa è la pre-demo di GASdottoNG. La piattaforma è ancora lungi dall'essere utilizzabile, ma questa istanza permette di vedere a che punto siamo arrivati.\n\nPer accedere:\nUtente amministratore: username: root, password: root\nUtente non privilegiato: username: user, password: user";
        $gas->save();

        User::create([
            'id' => str_slug('Utente Normale'),
            'gas_id' => $gas->id,
            'member_since' => date('Y-m-d', time()),
            'username' => 'user',
            'firstname' => 'Mario',
            'lastname' => 'Rossi',
            'email' => 'user@example.com',
            'password' => Hash::make('user'),
        ]);

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

        foreach ($data as $s_name => $products) {
            $s = Supplier::create([
                'id' => str_slug($s_name),
                'name' => $s_name,
                'email' => $s_name.'@example.com',
            ]);

            foreach ($products as $p_name) {
                Product::create([
                    'id' => str_slug($p_name),
                    'name' => $p_name,
                    'supplier_id' => $s->id,
                    'active' => true,
                    'price' => rand(200, 500) / 100,
                    'measure_id' => $measure->id,
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
