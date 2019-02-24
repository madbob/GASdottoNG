@include('documents.order_table_master', [
    'order' => $order,
    'selected_bookings' => $bookings,
    'get_function' => 'getDeliveredQuantity',
    'get_total' => 'delivered_with_friends',
    'get_function_real' => false
])
