<?php

namespace App\Importers\GDXP;

use App\Contact;

class Contacts extends GDXPImporter
{
    public static function readXML($xml)
    {
        // dummy
    }

    public static function importXML($xml, $parent)
    {
        foreach($xml->children() as $p) {
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

        $contact = new Contact();

        switch($json->type) {
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

        $contact->value = $json->value;

        $contact->target_id = $parent->id;
        $contact->target_type = get_class($parent);
        $contact->save();
    }
}
