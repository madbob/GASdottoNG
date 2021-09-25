<?php

namespace App\Importers\GDXP;

use App\Product;
use App\Variant;
use App\VariantValue;
use App\Category;
use App\Measure;
use App\VatRate;

class Products extends GDXPImporter
{
    public static function readXML($xml)
    {
        $product = new Product();

        foreach($xml->children() as $p) {
            switch($p->getName()) {
                case 'name':
                    $product->name = html_entity_decode((string) $p);
                    break;
            }
        }

        return $product;
    }

    public static function importXML($xml, $replace)
    {
        foreach($xml->children() as $p) {
            switch($p->getName()) {
                case 'sku':
                    $product->supplier_code = html_entity_decode((string) $p);
                    break;

                case 'name':
                    $product->name = html_entity_decode((string) $p);
                    break;

                case 'category':
                    $name = html_entity_decode((string) $p);
                    $category = Category::firstOrCreate(['name' => $name]);
                    $product->category_id = $category->id;
                    break;

                case 'um':
                    $name = html_entity_decode((string) $p);
                    $measure = Measure::firstOrCreate(['name' => $name]);
                    $product->measure_id = $measure->id;
                    break;

                case 'description':
                    $product->description = html_entity_decode((string) $p);
                    break;

                case 'active':
                    $product->active = (strtolower((string) $p) == 'true');
                    break;

                case 'orderInfo':
                    $map = [
                        'umPrice' => 'price',
                        'packageQty' => 'package_size',
                        'minQty' => 'min_quantity',
                        'maxQty' => 'max_quantity',
                    ];

                    foreach($p->children() as $e) {
                        /*
                            TODO: agganciare un modificatore che rappresenti il
                            costo di trasporto statico, valorizzato
                            nell'attributo "shippingCost"
                        */

                        $attr = $map[$e->getName()] ?? null;
                        if ($attr) {
                            $product->$attr = html_entity_decode((string) $e);
                        }
                    }
                    break;

                case 'variants':
                    $product->save();

                    foreach($p->children() as $e) {
                        $variant = new Variant();

                        foreach($e->attributes() as $attr_name => $attr_value) {
                            if ($attr_name == 'name') {
                                $variant->name = (string) $attr_value;
                            }
                        }

                        $variant->product_id = $product->id;
                        $variant->save();

                        foreach($e->children() as $i) {
                            $vv = new VariantValue();
                            $vv->variant_id = $variant->id;
                            $vv->value = html_entity_decode((string) $i);
                        }
                    }
                    break;
            }
        }

        return $product;
    }

    public static function readJSON($json)
    {
        $product = new Product();
        $product->name = $json->name;
        return $product;
    }

    public static function importJSON($master, $json, $replace)
    {
        if (is_null($replace)) {
            $product = new Product();
        }
        else {
            $product = Product::findOrFail($replace);
        }

        $product->name = $json->name;
        $product->supplier_code = $json->sku ?? '';
        $product->description = $json->description ?? '';
        $product->active = $json->active ?? true;
        $product->price = (float) ($json->orderInfo->umPrice ?? 0);

        $product->package_size = (float) ($json->orderInfo->packageQty ?? 0);
        if ($product->package_size == 1) {
            $product->package_size = 0;
        }

        $product->min_quantity = (float) ($json->orderInfo->minQty ?? 0);
        $product->max_quantity = (float) ($json->orderInfo->maxQty ?? 0);
        $product->multiple = (float) ($json->orderInfo->mulQty ?? 0);
        $product->transport = (float) ($json->orderInfo->shippingCost ?? 0);
        $product->max_available = (float) ($json->orderInfo->availableQty ?? 0);

        /*
            TODO: agganciare un modificatore che rappresenti il costo di
            trasporto statico, col valore di
            $json->orderInfo->shippingCost
        */

        $name = $json->category ?? '';
        if (!empty($name)) {
            $category = Category::firstOrCreate(['name' => $name]);
            $product->category_id = $category->id;
        }

        $name = $json->um ?? '';
        if (!empty($name)) {
            $measure = Measure::firstOrCreate(['name' => $name]);
            $product->measure_id = $measure->id;
        }

        $name = $json->orderInfo->vatRate ?? null;
        if (!is_null($name)) {
            $name = (float) $name;
            $vat_rate = VatRate::firstOrCreate(['percentage' => $name], ['name' => sprintf('%s%%', $name)]);
            $product->vat_rate_id = $vat_rate->id;
        }

        return $product;
    }
}
