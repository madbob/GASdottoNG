@include('documents.order_table_master', [
    'selected_bookings' => $order->topLevelBookings(),
    'get_function' => 'getDeliveredQuantity',
    'get_total' => 'delivered_with_friends',
    'get_function_real' => false
])
