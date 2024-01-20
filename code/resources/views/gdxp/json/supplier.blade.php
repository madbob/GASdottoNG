<?php

list($street, $city, $cap) = $obj->getAddress();

if (!isset($currentgas)) {
    $currentgas = currentAbsoluteGas();
}

if (!isset($order)) {
    $order = null;
}

if (!isset($bookings)) {
    $bookings = false;
}

if (function_exists('serializeTransformations') == false) {
    function serializeTransformations($target, $json_target)
    {
        $transformations = [];
        $modifiers = $target->modifiers->filter(fn($m) => $m->active && in_array($m->modifierType->identifier, ['shipping', 'discount']));

        foreach($modifiers as $mod) {
            if (in_array($mod->arithmetic, ['sum', 'sub'])) {
                $trans = (object) [
                    'type' => $mod->modifierType->identifier,
                    'operation' => $mod->arithmetic,
                ];

                $definitions = $mod->definitions;

                if ($mod->scale == 'minor') {
                    $shifted_definitions = [];
                    $prev_theshold = 0;

                    foreach($definitions as $def) {
                        $shifted_definitions[] = (object) [
                            'threshold' => $prev_theshold,
                            'amount' => $def->amount,
                        ];

                        $prev_theshold = $def->threshold;
                    }

                    $shifted_definitions[] = (object) [
                        'threshold' => $prev_theshold,
                        'amount' => 0,
                    ];

                    $definitions = $shifted_definitions;
                }

                if (count($definitions) == 1) {
                    $trans->fixed = formatPercentage($definitions[0]->amount, $mod->value == 'percentage');
                }
                else {
                    $trans->variable = (object) [
                        'theshold_type' => $mod->applies_type,
                        'thesholds' => [],
                    ];

                    foreach($definitions as $definition) {
                        $trans->variable->thesholds[] = (object) [
                            'theshold' => $definition->threshold,
                            'amount' => formatPercentage($definition->amount, $mod->value == 'percentage'),
                        ];
                    }
                }

                $json_target->transformations[] = $trans;
            }
        }

        if (empty($transformations) == false) {
            $json_target->transformations = $transformations;
        }

        return $json_target;
    }
}

$json_object = (object) [
    'protocolVersion' => 1.0,
    'creationDate' => date('Y-m-d'),
    'applicationSignature' => 'GASdotto',
    'subject' => (object) [
        'name' => $currentgas->name,
        'taxCode' => '',
        'vatNumber' => '',
        'address' => (object) [
            'street' => '',
            'locality' => '',
            'zipCode' => '',
        ],
        'contacts' => [
            (object) [
                'type' => 'emailAddress',
                'value' => $currentgas->email,
            ]
        ]
    ],
    'blocks' => [
        (object) [
            'supplier' => (object) [
                'name' => empty($obj->business_name) ? $obj->name : $obj->business_name,
                'taxCode' => $obj->taxcode,
                'vatNumber' => $obj->vat,
                'address' => (object) [
                    'street' => $street,
                    'locality' => $city,
                    'zipCode' => $cap,
                ],
                'contacts' => [],
                'products' => [],
            ]
        ]
    ]
];

foreach($obj->contacts as $contact) {
    switch($contact->type) {
        case 'phone':
            $type = 'phoneNumber';
            break;
        case 'fax':
            $type = 'faxNumber';
            break;
        case 'email':
            $type = 'emailAddress';
            break;
        case 'website':
            $type = 'webSite';
            break;
        default:
            $type = '';
            break;
    }

    if (!empty($type)) {
        $json_object->blocks[0]->supplier->contacts[] = (object) [
            'type' => $type,
            'value' => $contact->value
        ];
    }
}

if ($order && $bookings) {
    $summary = $order->reduxData();
}

foreach($obj->products as $product) {
    $p = (object) [
        'name' => $product->name,
        'um' => $product->measure->name,
        'sku' => $product->supplier_code ?? '',
        'category' => $product->category->name,
        'description' => $product->description ?? '',
        'orderInfo' => (object) [
            'packageQty' => (integer) ($product->package_size > 1 ? $product->package_size : 1),
            'maxQty' => (float) $product->max_quantity,
            'minQty' => (float) ($product->min_quantity > 0 ? $product->min_quantity : 1),
            'mulQty' => (float) $product->multiple,
            'availableQty' => (float) $product->max_available,
            'umPrice' => (float) $product->price,
        ],
        'active' => $product->active,
    ];

    if (empty($product->picture) == false) {
        $p->attachments = [
            (object) [
                'name' => 'Immagine',
                'contents' => base64_encode(file_get_contents(gas_storage_path($product->picture))),
            ],
        ];
    }

    if ($product->vat_rate) {
        $p->orderInfo->vatRate = $product->vat_rate->percentage;
    }

    if ($bookings) {
        if (isset($summary->products[$product->id])) {
            $p->bookingInfo = (object) [
                'totalQty' => (float) $summary->products[$product->id]->quantity ?? 0,
            ];
        }
        else {
            $p->bookingInfo = (object) [
                'totalQty' => 0,
            ];
        }
    }

    $p = serializeTransformations($product, $p);

    $json_object->blocks[0]->supplier->products[] = $p;
}

$json_object->blocks[0]->supplier = serializeTransformations($obj, $json_object->blocks[0]->supplier);

$attachments = $obj->attachments->filter(fn($a) => $a->internal == false);
if ($attachments->isEmpty() == false) {
    $json_object->blocks[0]->supplier->attachments = [];

    foreach($attachments as $attachment) {
        $json_object->blocks[0]->supplier->attachments[] = (object) [
            'name' => $attachment->name,
            'contents' => base64_encode(file_get_contents($attachment->path)),
        ];
    }
}

if ($order) {
    $json_object->blocks[0]->orderInfo = (object) [
        'phase' => $bookings ? 'booking' : 'order',
        'openDate' => $order->start,
        'closeDate' => $order->end,
        'deliveryDate' => $order->shipping,
    ];
}

echo json_encode($json_object);
