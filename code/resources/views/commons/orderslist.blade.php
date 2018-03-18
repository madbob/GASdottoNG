<?php

$params = [
    'identifier' => 'booking-list',
    'items' => $orders,
    'url' => 'bookings',
    'header_function' => 'printableUserHeader',
];

if (isset($no_legend) == false) {
    $params['legend'] = (object)[
        'class' => 'Aggregate'
    ];
}

?>

@include('commons.loadablelist', $params)
