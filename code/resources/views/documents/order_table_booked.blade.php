@include('documents.order_table_master', [
    'order' => $order,
    'selected_bookings' => $bookings,
    'get_function' => 'getBookedQuantity',
    'get_total' => 'value_with_friends',
    'get_function_real' => true
])
