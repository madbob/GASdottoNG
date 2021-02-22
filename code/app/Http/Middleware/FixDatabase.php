<?php

namespace App\Http\Middleware;

use DB;
use Log;
use Closure;

use App\Product;
use App\Order;
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
    private function createEmptyModifier($product, $modifier_type)
    {
        $modifier = new Modifier();
        $modifier->modifier_type_id = $modifier_type;
        $modifier->target_type = get_class($product);
        $modifier->target_id = $product->id;
        $modifier->definition = '[]';
        $modifier->save();
        return $modifier;
    }

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

    private function fixOrders($order_attribute, $modifier_type)
    {
        foreach(Order::where($order_attribute, '!=', 0)->get() as $order) {
            if (isPercentage($order->$order_attribute)) {
                $type = 'percentage';
                $amount = (float) $order->$order_attribute;
            }
            else {
                $type = 'absolute';
                $amount = $order->$order_attribute;
            }

            $modifier = new Modifier();
            $modifier->modifier_type_id = $modifier_type;
            $modifier->target_type = get_class($order);
            $modifier->target_id = $order->id;
            $modifier->value = $type;
            $modifier->arithmetic = 'sum';
            $modifier->scale = 'minor';
            $modifier->applies_type = 'none';
            $modifier->applies_target = 'order';
            $modifier->distribution_type = 'price';
            $modifier->definition = '[{"threshold":9223372036854775807,"amount":"' . $amount . '"}]';
            $modifier->save();
        }
    }

    private function fixBooked($product_attribute, $modifier_type)
    {
        $cache = [];

        foreach(BookedProduct::where($product_attribute, '!=', 0)->get() as $product) {
            if (!isset($cache[$product->product_id])) {
                $modifier = $product->product->modifiers()->where('modifier_type_id', $modifier_type)->first();
                if (is_null($modifier)) {
                    $modifier = $this->createEmptyModifier($product->product, $modifier_type);
                }

                $cache[$product->product_id] = $modifier;
            }

            $modifier = new ModifiedValue();
            $modifier->modifier_id = $cache[$product->product_id]->id;
            $modifier->target_type = get_class($product);
            $modifier->target_id = $product->id;
            $modifier->amount = $product->$product_attribute;
            $modifier->created_at = $product->updated_at;
            $modifier->updated_at = $product->updated_at;
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
                $this->fixProducts('transport', 'spese-trasporto');
                $this->fixOrders('discount', 'sconto');
                $this->fixOrders('transport', 'spese-trasporto');
                $this->fixBooked('final_discount', 'sconto');
                $this->fixBooked('final_transport', 'spese-trasporto');

                DB::commit();
            }
            catch(\Exception $e) {
                Log::error('Impossibile adattare i modificatori sul DB: ' . $e->getMessage() . ' / ' . $e->getLine());
            }
        }

        return $next($request);
    }
}
