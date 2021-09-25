<?php

namespace App\Importers\GDXP;

use Illuminate\Support\Collection;

use App\Supplier;
use App\Contact;
use App\Aggregate;

class Suppliers extends GDXPImporter
{
    public static function readXML($xml)
    {
        $supplier = new Supplier();
        $supplier->products = new Collection();
        $supplier->orders = new Collection();

        foreach($xml->children() as $c) {
            switch($c->getName()) {
                case 'name':
                    $supplier->name = $supplier->business_name = html_entity_decode((string) $c);
                    break;

                case 'products':
                    foreach($c->children() as $a) {
                        $product = Products::readXML($a);
                        $supplier->products->push($product);
                    }
                    break;

                case 'orders':
                    foreach($c->children() as $a) {
                        $order = Orders::readXML($a);
                        $supplier->orders->push($order);
                    }
                    break;
            }
        }

        return $supplier;
    }

    public static function importXML($xml, $replace)
    {
        if (is_null($replace)) {
            $supplier = new Supplier();
            $supplier->payment_method = '';
            $supplier->order_method = '';
            $supplier->save();
        }
        else {
            $supplier = Supplier::findOrFail($replace);
            $supplier->contacts()->delete();
        }

        $product_ids = [];

        foreach($xml->children() as $c) {
            switch($c->getName()) {
                case 'taxCode':
                    $supplier->taxcode = html_entity_decode((string) $c);
                    break;

                case 'vatNumber':
                    $supplier->vat = html_entity_decode((string) $c);
                    break;

                case 'name':
                    $name = $supplier->business_name = html_entity_decode((string) $c);

                    /*
                        Per evitare collisioni sui nomi dei fornitori
                    */
                    $index = 2;
                    while(Supplier::where('name', $name)->first() != null)
                        $name = $supplier->business_name . ' ' . $index++;

                    $supplier->name = $name;
                    break;

                case 'contacts':
                    foreach($c->children() as $a) {
                        foreach($a->children() as $p) {
                            foreach($p->children() as $e) {
                                $contact = new Contact();

                                switch($e->getName()) {
                                    case 'phoneNumber':
                                        $contact->type = 'phone';
                                        break;
                                    case 'faxNumber':
                                        $contact->type = 'fax';
                                        break;
                                    case 'emailAddress':
                                        $contact->type = 'email';
                                        break;
                                    case 'webSite':
                                        $contact->type = 'website';
                                        break;
                                }

                                $contact->value = html_entity_decode((string) $e);

                                $contact->target_id = $supplier->id;
                                $contact->target_type = get_class($supplier);
                                $contact->save();
                            }
                        }
                    }
                    break;

                case 'products':
                    foreach($c->children() as $a) {
                        $ex_product = $supplier->products()->where('name', $product_name)->first();
                        $product = Products::importXML($a, $ex_product->id ?? null);
                        $product->supplier_id = $supplier->id;
                        $product->save();
                        $product_ids[] = $product->id;
                    }
                    break;

                case 'orders':
                    foreach($c->children() as $a) {
                        $aggregate = new Aggregate();
                        $aggregate->save();

                        $order = Orders::readXML($a);
                        $order->supplier_id = $supplier->id;
                        $order->aggregate_id = $aggregate->id;
                        $order->status = 'closed';
                        $order->save();
                        $order->products()->attach($product_ids);
                    }
                    break;
            }
        }

        return $supplier;
    }

    public static function readJSON($json)
    {
        $supplier = new Supplier();

        $supplier->name = $json->name;
        $supplier->vat = $json->vatNumber ?? '';

        $supplier->products = new Collection();
        foreach($json->products as $a) {
            $product = Products::readJSON($a);
            $supplier->products->push($product);
        }

        $supplier->orders = new Collection();
        if (isset($json->order)) {
            $order = Orders::readJSON($json->order);
            $supplier->orders->push($order);
        }

        return $supplier;
    }

    public static function importJSON($master, $json, $replace)
    {
        if (is_null($replace)) {
            $supplier = new Supplier();
            $supplier->payment_method = '';
            $supplier->order_method = '';
        }
        else {
            $supplier = Supplier::findOrFail($replace);
            $supplier->contacts()->delete();
        }

        $product_ids = [];

        $supplier->name = $json->name;
        $supplier->remote_lastimport = $master->creationDate ?? date('Y-m-d');
        $supplier->taxcode = $json->taxCode ?? '';
        $supplier->vat = $json->vatNumber ?? '';
        $supplier->save();

        foreach($json->contacts as $c) {
            if (empty($c->value)) {
                continue;
            }

            $contact = new Contact();

            switch($c->type) {
                case 'phoneNumber':
                    $contact->type = 'phone';
                    break;
                case 'faxNumber':
                    $contact->type = 'fax';
                    break;
                case 'emailAddress':
                    $contact->type = 'email';
                    break;
                case 'webSite':
                    $contact->type = 'website';
                    break;
            }

            $contact->value = $c->value;

            $contact->target_id = $supplier->id;
            $contact->target_type = get_class($supplier);
            $contact->save();
        }

        if (!empty($json->address->locality)) {
            $contact = new Contact();
            $contact->type = 'address';
            $contact->value = normalizeAddress($json->address->street, $json->address->locality, $json->address->zipCode);
            $contact->target_id = $supplier->id;
            $contact->target_type = get_class($supplier);
            $contact->save();
        }

        foreach($json->products as $json_product) {
            $ex_product = $supplier->products()->where('name', $json_product->name)->first();
            $product = Products::importJSON($master, $json_product, $ex_product->id ?? null);
            $product->supplier_id = $supplier->id;
            $product->save();
        }

        return $supplier;
    }
}
