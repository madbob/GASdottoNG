@include('documents.order_table_master', [
    'selected_bookings' => $order->bookings()->where('status', 'saved')->get(),
    'get_function' => 'getBookedQuantity',
    'get_function_real' => true
])
