<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;

use App\Gas;
use App\User;
use App\Role;
use App\Supplier;
use App\Aggregate;
use App\Order;
use App\Product;
use App\Variant;
use App\VariantValue;
use App\Date;
use App\Measure;
use App\Category;
use App\VatRate;
use App\Modifier;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $gas = Gas::where('name', '!=', '')->first();

        $gas->name = 'GAS Demo';
        $gas->message = "Questa istanza permette di avere una idea del funzionamento di GASdottoNG.\n\nPer accedere:\nUtente amministratore: username: root, password: password\nUtente non privilegiato: username: user, password: password\n\nL'inoltro di messaggi email da questa istanza è deliberatamente disabilitato, per evitare abusi.\n\nQuesta istanza viene quotidianamente rinnovata con le ultimissime modifiche (al contrario delle istanze hostate su gasdotto.net, sulle quali viene condotto qualche test in più prima della pubblicazione). GASdottoNG è un progetto in continua evoluzione: se noti qualcosa che non va, o una funzione che manca, mandaci una mail a info@madbob.org";
        $gas->save();

        $group = app()->make('GroupsService')->store([
            'name' => 'Luoghi di Consegna',
        ]);

        $circle = app()->make('CirclesService')->store([
            'name' => 'Bar Sport',
            'description' => '',
            'group_id' => $group->id,
        ]);

        $circle = app()->make('CirclesService')->store([
            'name' => 'Da Mario',
            'description' => 'Un altro test',
            'group_id' => $group->id,
        ]);

        $users = [
            ['user', 'Giuseppe', 'Garibaldi'],
            ['verdi', 'Giuseppe', 'Verdi'],
            ['mazzini', 'Giuseppe', 'Mazzini'],
            ['azeglio', 'Massimo', "D'Azeglio"],
            ['pisacane', 'Carlo', 'Pisacane'],
            ['bandiera', 'Attilio', 'Bandiera'],
            ['benso', 'Camillo', 'Benso'],
        ];

        foreach ($users as $user) {
            $u = new User();
            $u->gas_id = $gas->id;
            $u->member_since = date('Y-m-d');
            $u->username = $user[0];
            $u->firstname = $user[1];
            $u->lastname = $user[2];
            $u->password = Hash::make('password');
            $u->save();
        }

        $role = roleByIdentifier('user');
        app()->make('RolesService')->attachAction($role->id, 'users.subusers');

        $referrer_role = Role::where('name', 'Referente')->first();
        $administrator = User::where('username', 'root')->first();
        $administrator->password = Hash::make('password');
        $administrator->save();

        $u = new User();
        $u->gas_id = $gas->id;
        $u->parent_id = $administrator->id;
        $u->member_since = date('Y-m-d');
        $u->username = 'luigi';
        $u->firstname = 'Luigi';
        $u->lastname = 'Verdi';
        $u->password = Hash::make('password');
        $u->save();

        $suppliers = [
            ['La Zucchina Dorata', 'Verdure di stagione, prenotazioni settimanali', 'Bonifico bancario IBAN IT01234567890', 'Mandare una mail con le prenotazioni a Luisa: zucchina@example.com', 'IT01234567890'],
            ['Mele e Pere', '', '', '', ''],
            ['Panetteria da Pasquale', '', '', '', ''],
            ['Luigi il Macellaio', '', '', '', ''],
        ];

        foreach ($suppliers as $index => $data) {
            $s = Supplier::create([
                'name' => $data[0],
                'description' => $data[1],
                'payment_method' => $data[2],
                'order_method' => $data[3],
                'vat' => $data[4],
            ]);

            $gas->suppliers()->attach($s->id);

            if ($index == 0) {
                $category = Category::where('name', 'Verdura')->first();
                $kg_measure = Measure::where('name', 'Chili')->first();
                $portion_measure = Measure::where('name', 'Pezzi')->first();
                $vat_rate = VatRate::inRandomOrder()->first();

                $products = [
                    (object) [
                        'name' => 'Finocchi',
                        'price' => 3.00,
                        'unit_measure' => $kg_measure->id,
                        'category' => $category->id,
                        'min_quantity' => 3,
                        'variants' => [],
                    ],
                    (object) [
                        'name' => 'Melanzane',
                        'price' => 2.00,
                        'unit_measure' => $kg_measure->id,
                        'category' => $category->id,
                        'variants' => [
                            'Forma' => ['Tonda', 'Ovale', 'Lunga'],
                            'Colore' => ['Nera', 'Viola'],
                        ],
                    ],
                    (object) [
                        'name' => 'Peperoncino piccante',
                        'price' => 0.50,
                        'unit_measure' => $portion_measure->id,
                        'category' => $category->id,
                        'variants' => [
                            'Piccantezza' => ['Poco piccante', 'Molto piccante'],
                        ],
                    ],
                    (object) [
                        'name' => 'Zucchine',
                        'price' => 2.50,
                        'unit_measure' => $kg_measure->id,
                        'category' => $category->id,
                        'max_available' => 3,
                        'variants' => [],
                    ],
                ];

                foreach ($products as $index => $p) {
                    $prod = Product::create([
                        'name' => $p->name,
                        'supplier_id' => $s->id,
                        'active' => true,
                        'price' => $p->price,
                        'measure_id' => $p->unit_measure,
                        'category_id' => $p->category,
                        'vat_rate_id' => $vat_rate->id,
                    ]);

                    foreach ($p->variants as $name => $values) {
                        $v = new Variant();
                        $v->name = $name;
                        $v->product_id = $prod->id;
                        $v->save();

                        foreach ($values as $value) {
                            $val = new VariantValue();
                            $val->variant_id = $v->id;
                            $val->value = $value;
                            $val->save();
                        }
                    }

                    if ($index == 0) {
                        $mod = new Modifier();
                        $mod->modifier_type_id = 'sconto';
                        $mod->target_type = Product::class;
                        $mod->target_id = $prod->id;
                        $mod->value = 'price';
                        $mod->arithmetic = 'apply';
                        $mod->scale = 'major';
                        $mod->applies_type = 'quantity';
                        $mod->applies_target = 'order';
                        $mod->definition = '[{"threshold":"40","amount":"2.20"},{"threshold":"20","amount":"2.50"}]';
                        $mod->save();
                    }

                    if ($index == 2) {
                        $mod = new Modifier();
                        $mod->modifier_type_id = 'sconto';
                        $mod->target_type = Product::class;
                        $mod->target_id = $prod->id;
                        $mod->value = 'percentage';
                        $mod->arithmetic = 'sub';
                        $mod->scale = 'major';
                        $mod->applies_type = 'quantity';
                        $mod->applies_target = 'product';
                        $mod->definition = '[{"threshold":"15","amount":"20"},{"threshold":"5","amount":"10"}]';
                        $mod->save();
                    }

                    if ($index == 3) {
                        $mod = new Modifier();
                        $mod->modifier_type_id = 'sconto';
                        $mod->target_type = Product::class;
                        $mod->target_id = $prod->id;
                        $mod->value = 'percentage';
                        $mod->arithmetic = 'sub';
                        $mod->applies_type = 'product';
                        $mod->applies_target = 'product';
                        $mod->definition = '[{"threshold":9223372036854775807,"amount":"5"}]';
                        $mod->save();
                    }
                }

                $aggregate = Aggregate::create([]);

                Order::create([
                    'supplier_id' => $s->id,
                    'aggregate_id' => $aggregate->id,
                    'status' => 'open',
                    'start' => Carbon::today()->subDays(2)->format('Y-m-d'),
                    'end' => Carbon::today()->addDays(10)->format('Y-m-d'),
                    'shipping' => Carbon::today()->addDays(15)->format('Y-m-d'),
                ]);
            }

            $administrator->addRole($referrer_role, $s);
        }
    }
}
