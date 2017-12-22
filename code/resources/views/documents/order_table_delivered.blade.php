@include('documents.order_table_master', [
    'selected_bookings' => $order->bookings,
    'get_function' => 'getDeliveredQuantity',
    'get_total' => 'delivered',
    'get_function_real' => false
])
