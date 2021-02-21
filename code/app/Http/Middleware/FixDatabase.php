<?php

namespace App\Http\Middleware;

use DB;
use Closure;

use App\Product;
use App\BookedProduct;
use App\Modifier;
use App\ModifiedValue;
use App\ModifierType;

/*
    Questo middleware è destinato ad ospitare eventuali correzioni "in corsa" al
    database, per creare o ricreare elementi che per default dovrebbero sempre
    esserci
*/
class FixDatabase
{
    private function fixProducts($product_attribute, $modifier_type)
    {
        foreach(Product::where($product_attribute, '!=', 0)->get() as $product) {
            if (isPercentage($product->$product_attribute)) {
                $type = 'percentage';
                $amount = (float) $product->$product_attribute;
            }
            else {
                $type = 'absolute';
                $amount = $product->$product_attribute;
            }

            $modifier = new Modifier();
            $modifier->modifier_type_id = $modifier_type;
            $modifier->target_type = get_class($product);
            $modifier->target_id = $product->id;
            $modifier->value = $type;
            $modifier->arithmetic = 'sum';
            $modifier->scale = 'minor';
            $modifier->applies_type = 'none';
            $modifier->applies_target = 'product';
            $modifier->distribution_type = 'none';
            $modifier->definition = '[{"threshold":9223372036854775807,"amount":"' . $amount . '"}]';
            $modifier->save();
        }
    }

    private function fixBooked($product_attribute, $modifier_type)
    {
        $cache = [];

        foreach(BookedProduct::where($product_attribute, '!=', 0)->get() as $product) {
            if (!isset($cache[$product->product_id])) {
                $cache[$product->product_id] = $product->product->modifiers()->where('modifier_type_id', $modifier_type)->first();
            }

            $modifier = new ModifiedValue();
            $modifier->modifier_id = $cache[$product->product_id]->id;
            $modifier->target_type = get_class($product);
            $modifier->target_id = $product->id;
            $modifier->amount = $product->$product_attribute;
            $modifier->save();
        }
    }

    public function handle($request, Closure $next)
    {
        if (ModifierType::all()->isEmpty()) {
            try {
                DB::beginTransaction();

                $m = new ModifierType();
                $m->id = 'spese-trasporto';
                $m->name = _i('Spese Trasporto');
                $m->system = true;
                $m->classes = ['App\Product', 'App\Supplier'];
                $m->save();

                $m = new ModifierType();
                $m->id = 'sconto';
                $m->name = _i('Sconto');
                $m->system = true;
                $m->classes = ['App\Product', 'App\Supplier'];
                $m->save();

                $this->fixProducts('discount', 'sconto');
                $this->fixProducts('shipping', 'spese-trasporto');
                $this->fixBooked('final_discount', 'sconto');
                $this->fixBooked('final_shipping', 'spese-trasporto');

                DB::commit();
            }
            catch(\Exception $e) {
                Log::error('Impossibile adattare i modificatori sul DB: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
