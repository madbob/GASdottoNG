<?php

namespace App\Importers\GDXP;

use Illuminate\Support\Str;

use App\Product;

class Attachments extends GDXPImporter
{
    public static function readXML($xml)
    {
        return null;
    }

    public static function importXML($xml, $replace)
    {
        return null;
    }

    public static function readJSON($json)
    {
        return null;
    }

    public static function importJSON($target, $json)
    {
        $contents = base64_decode($json->contents);

        if (is_a($target, Product::class)) {
            $filename = Str::random(30);
            $fullpath = sprintf('%s/%s', gas_storage_path('app'), $filename);
            file_put_contents($fullpath, $contents);

            $mime = mime_content_type($fullpath);
            if (str_starts_with($mime, 'image')) {
                $target->picture = $fullpath;
            }
            else {
                /*
                    TODO: al momento non viene gestito nessun altro file
                    allegato ai prodotti oltre all'immagine
                */
                @unlink($fullpath);
            }

            return null;
        }
        else {
            $existing = $target->attachments()->where('name', $json->name)->first();

            return $target->attachByContents($json->name, $contents, $existing ? $existing->id : null);
        }
    }
}
