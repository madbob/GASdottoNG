<?php

Route::get('/', function () {
	return Redirect::to('/dashboard');
});

Route::get('/home', function () {
	return Redirect::to('/dashboard');
});

Route::get('users/search', 'UsersController@search');

Route::resource('users', 'UsersController');
Route::resource('suppliers', 'SuppliersController');
Route::resource('products', 'ProductsController');
Route::resource('variants', 'VariantsController');
Route::resource('orders', 'OrdersController');
Route::resource('booking.user', 'BookingUserController');
Route::resource('booking', 'BookingController');
Route::resource('notifications', 'NotificationsController');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
	'dashboard' => 'CommonsController'
]);
