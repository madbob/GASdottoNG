<?php

Route::group(array('prefix' => 'api/1'), function () {
    Route::get('users/search', 'REST\UsersController@search');
    Route::resource('users', 'REST\UsersController');
});

Route::get('/', function () {
    return Redirect::to('/dashboard');
});

Route::get('/home', function () {
    return Redirect::to('/dashboard');
});

Route::get('users/search', 'UsersController@search');
Route::get('users/profile', 'UsersController@profile');
Route::get('roles/user/{user_id}', 'RolesController@formByUser');
Route::get('roles/supplier/{supplier_id}', 'RolesController@formBySupplier');
Route::post('roles/attach', 'RolesController@attach');
Route::post('roles/detach', 'RolesController@detach');
Route::post('notifications/markread/{id}', 'NotificationsController@markread');
Route::get('attachments/download/{id}', 'AttachmentsController@download');
Route::get('orders/search', 'OrdersController@search');
Route::get('orders/recalculate/{id}', 'OrdersController@recalculate');
Route::post('orders/fixes/{id}', 'OrdersController@fixes');
Route::get('orders/document/{id}/{type}/{subtype?}', 'OrdersController@document');
Route::post('products/massiveupdate', 'ProductsController@massiveUpdate');
Route::get('suppliers/catalogue/{id}/{format}', 'SuppliersController@catalogue');
Route::get('suppliers/{id}/plain_balance', 'SuppliersController@plainBalance');
Route::get('movements/balance', 'MovementsController@getBalance');
Route::post('movements/recalculate', 'MovementsController@recalculate');
Route::post('movements/close', 'MovementsController@closeBalance');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
    'dashboard' => 'CommonsController',
    'import' => 'ImportController',
]);

Route::get('gas/{id}/header', 'GasController@objhead');
Route::get('users/{id}/header', 'UsersController@objhead');
Route::get('roles/{id}/header', 'RolesController@objhead');
Route::get('suppliers/{id}/header', 'SuppliersController@objhead');
Route::get('products/{id}/header', 'ProductsController@objhead');
Route::get('vatrates/{id}/header', 'VatRatesController@objhead');
Route::get('deliveries/{id}/header', 'DeliveriesController@objhead');
Route::get('categories/{id}/header', 'CategoriesController@objhead');
Route::get('measures/{id}/header', 'MeasuresController@objhead');
Route::get('variants/{id}/header', 'VariantsController@objhead');
Route::get('orders/{id}/header', 'OrdersController@objhead');
Route::get('aggregates/{id}/header', 'AggregatesController@objhead');
Route::get('attachments/{id}/header', 'AttachmentsController@objhead');
Route::get('bookings/{id}/header', 'BookingController@objhead');
Route::get('booking/{booking_id}/user/{user_id}/header', 'BookingUserController@objhead2');
Route::get('delivery/{aggregate_id}/user/{user_id}/header', 'DeliveryUserController@objhead2');
Route::get('booking/{id}/header', 'BookingController@objhead');
Route::get('notifications/{id}/header', 'NotificationsController@objhead');
Route::get('movements/{id}/header', 'MovementsController@objhead');

Route::resource('gas', 'GasController');
Route::resource('users', 'UsersController');
Route::resource('roles', 'RolesController');
Route::resource('suppliers', 'SuppliersController');
Route::resource('products', 'ProductsController');
Route::resource('vatrates', 'VatRatesController');
Route::resource('deliveries', 'DeliveriesController');
Route::resource('categories', 'CategoriesController');
Route::resource('measures', 'MeasuresController');
Route::resource('variants', 'VariantsController');
Route::resource('orders', 'OrdersController');
Route::resource('aggregates', 'AggregatesController');
Route::resource('attachments', 'AttachmentsController');
Route::resource('booking.user', 'BookingUserController');
Route::resource('delivery.user', 'DeliveryUserController');
Route::resource('booking', 'BookingController');
Route::resource('bookings', 'BookingController');
Route::resource('notifications', 'NotificationsController');
Route::resource('movements', 'MovementsController');
Route::resource('stats', 'StatisticsController');
