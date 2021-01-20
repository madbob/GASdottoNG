<?php

list($street, $city, $cap) = $obj->getAddress();

if (!isset($currentgas)) {
    $currentgas = currentAbsoluteGas();
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
            'shippingCost' => (float) $product->transport,
        ],
        'active' => $product->active,
    ];

    if ($product->vat_rate) {
        $p->orderInfo->vatRate = $product->vat_rate->percentage;
    }

    $json_object->blocks[0]->supplier->products[] = $p;
}

echo json_encode($json_object);
