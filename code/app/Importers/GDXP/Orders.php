<?php

namespace App\Importers\GDXP;

use App\Order;

class Orders extends GDXPImporter
{
    public static function readXML($xml)
    {
        $order = new Order();

        foreach ($xml->children() as $p) {
            switch ($p->getName()) {
                case 'openDate':
                    $order->start = self::xmlDateFormat((string) $p);
                    break;
                case 'closeDate':
                    $order->end = self::xmlDateFormat((string) $p);
                    break;
                case 'deliveryDate':
                    $order->shipping = self::xmlDateFormat((string) $p);
                    break;
                default:
                    \Log::warning('Attributo GDXP non riconosciuto: ' . $p->getName());
                    break;
            }
        }

        return $order;
    }

    public static function readJSON($json)
    {
        $order = new Order();
        $order->start = $json->openDate;
        $order->end = $json->closeDate;
        $order->shipping = $json->deliveryDate ?? null;

        return $order;
    }
}
