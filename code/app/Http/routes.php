<?php

Route::get('/', function () {
	return Redirect::to('/dashboard');
});

Route::get('/home', function () {
	return Redirect::to('/dashboard');
});

Route::resource('users', 'UsersController');
Route::resource('suppliers', 'SuppliersController');
Route::resource('products', 'ProductsController');
Route::resource('orders', 'OrdersController');
Route::resource('booking', 'BookingController');
Route::resource('notifications', 'NotificationsController');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
	'dashboard' => 'CommonsController'
]);
