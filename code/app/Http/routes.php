<?php

Route::group(array('prefix' => 'api/1'), function(){
	Route::resource('users', 'REST\UsersController');
});
		
Route::get('/', function () {
	return Redirect::to('/dashboard');
});

Route::get('/home', function () {
	return Redirect::to('/dashboard');
});

Route::get('users/search', 'UsersController@search');
Route::post('notifications/markread/{id}', 'NotificationsController@markread');
Route::get('attachments/download/{id}', 'AttachmentsController@download');
Route::get('orders/search', 'OrdersController@search');
Route::post('orders/fixes/{id}', 'OrdersController@fixes');
Route::get('orders/document/{id}/{type}', 'OrdersController@document');
Route::get('suppliers/catalogue/{id}/{format}', 'SuppliersController@catalogue');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
	'dashboard' => 'CommonsController',
	'import' => 'ImportController',
	'permissions' => 'PermissionsController',
]);

Route::resource('gas', 'GasController');
Route::resource('users', 'UsersController');
Route::resource('suppliers', 'SuppliersController');
Route::resource('products', 'ProductsController');
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
