@include('documents.order_table_master', [
    'order' => $order,
    'selected_bookings' => $bookings,
    'get_function' => 'getDeliveredQuantity',
    'get_total' => 'delivered',
    'with_friends' => true,
    'get_function_real' => false
])
