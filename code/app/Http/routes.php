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
Route::post('roles/attach', 'RolesController@attach');
Route::post('roles/detach', 'RolesController@detach');
Route::post('notifications/markread/{id}', 'NotificationsController@markread');
Route::get('attachments/download/{id}', 'AttachmentsController@download');
Route::get('orders/search', 'OrdersController@search');
Route::post('orders/fixes/{id}', 'OrdersController@fixes');
Route::get('orders/document/{id}/{type}', 'OrdersController@document');
Route::post('products/massiveupdate', 'ProductsController@massiveUpdate');
Route::get('suppliers/catalogue/{id}/{format}', 'SuppliersController@catalogue');
Route::get('suppliers/{id}/plain_balance', 'SuppliersController@plainBalance');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
    'dashboard' => 'CommonsController',
    'import' => 'ImportController',
]);

Route::resource('gas', 'GasController');
Route::resource('users', 'UsersController');
Route::resource('roles', 'RolesController');
Route::resource('suppliers', 'SuppliersController');
Route::resource('products', 'ProductsController');
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
Route::resource('notifications', 'NotificationsController');
Route::resource('movements', 'MovementsController');
Route::resource('stats', 'StatisticsController');
