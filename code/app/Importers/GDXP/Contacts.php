<?php

namespace App\Importers\GDXP;

use App\Contact;

class Contacts extends GDXPImporter
{
    public static function readXML($xml)
    {
        // dummy
    }

    private static function map($attribute)
    {
        $map = [
            'phoneNumber' => 'phone',
            'faxNumber' => 'fax',
            'emailAddress' => 'email',
            'webSite' => 'website',
        ];

        return $map[$attribute];
    }

    public static function importXML($xml, $parent)
    {
        foreach($xml->children() as $p) {
            foreach($p->children() as $e) {
                $type = self::map($e->getName());
                if (is_null($type)) {
                    continue;
                }

                $contact = new Contact();
                $contact->type = $type;
                $contact->value = html_entity_decode((string) $e);
                $contact->target_id = $parent->id;
                $contact->target_type = get_class($parent);
                $contact->save();
            }
        }
    }

    public static function readJSON($json)
    {
        // dummy
    }

    public static function importJSON($json, $parent)
    {
        if (empty($json->value)) {
            return;
        }

        $type = self::map($json->type);
        if (is_null($type)) {
            return;
        }

        $contact = new Contact();
        $contact->type = $type;
        $contact->value = $json->value;
        $contact->target_id = $parent->id;
        $contact->target_type = get_class($parent);
        $contact->save();
    }
}
