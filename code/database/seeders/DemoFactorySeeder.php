<?php

namespace Database\Seeders;

use App\ModifierType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Gas;
use App\User;
use App\Role;
use App\Supplier;
use App\Aggregate;
use App\Order;
use App\Product;
use App\Variant;
use App\VariantValue;
use App\Delivery;
use App\Modifier;

class DemoFactorySeeder extends Seeder
{
    public function run()
    {
        $gas = Gas::firstOrFail();
        $gas->name = 'GAS Demo';
        $gas->message = "Questa istanza permette di avere una idea del funzionamento di GASdottoNG.\n\nPer accedere:\nUtente amministratore: username: root, password: password\nUtente non privilegiato: username: user, password: password\n\nL'inoltro di messaggi email da questa istanza è deliberatamente disabilitato, per evitare abusi.\n\nQuesta istanza viene quotidianamente rinnovata con le ultimissime modifiche (al contrario delle istanze hostate su gasdotto.net, sulle quali viene condotto qualche test in più prima della pubblicazione). GASdottoNG è un progetto in continua evoluzione: se noti qualcosa che non va, o una funzione che manca, mandaci una mail a info@madbob.org";
        $gas->save();

        User::factory(10)
            ->recycle($gas)
            ->create(['gas_id' => $gas->id]);

        $del = Delivery::factory()
            ->recycle($gas)
            ->create(['default' => 1]);

        Modifier::factory()
            ->shipping($del)
            ->create([
                'modifier_type_id' => ModifierType::where('identifier', 'shipping')->first()->id,
            ]);

        $role = roleByIdentifier('user');
        app()->make('RolesService')->attachAction($role->id, 'users.subusers');

        $referrer_role = Role::where('name', 'Referente')->first();
        $administrator = User::where('username', 'root')->first();
        $administrator->password = Hash::make('password');
        $administrator->save();

        $u = User::factory()->create(
            [
                'gas_id' => $gas->id,
                'parent_id' => $administrator->id,
                'username' => 'luigi',
                'firstname' => 'Luigi',
                'lastname' => 'Verdi',
            ]
        );

        $suppliers = Supplier::factory(4)->ita()->create();

        $products = Product::factory(10)
            ->recycle($suppliers)
            ->ita()
            ->create();


        foreach ($products as $product) {
            foreach ($product->variants as $name => $values) {
                $v = new Variant();
                $v->name = $name;
                $v->product_id = $product->id;
                $v->save();

                foreach ($values as $value) {
                    $val = new VariantValue();
                    $val->variant_id = $v->id;
                    $val->value = $value;
                    $val->save();
                }
            }
            Modifier::factory()
                ->discount($product)
                ->create(
                    [
                        'modifier_type_id' => ModifierType::where('identifier', 'discount')->first()->id,
                    ]
                );
        }

        $aggregate = Aggregate::create([]);

        Order::factory()
            ->recycle($suppliers)
            ->recycle($aggregate)
            ->create();

        $administrator->addRole($referrer_role, $u);
    }
}
