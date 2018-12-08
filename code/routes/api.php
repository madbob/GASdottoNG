<?php

use Illuminate\Http\Request;

Route::group(array('prefix' => '1'), function () {
    Route::get('users/search', 'REST\UsersController@search');
    Route::resource('users', 'REST\UsersController');
    Route::resource('suppliers', 'REST\SuppliersController');
    Route::resource('products', 'REST\ProductsController');
    Route::resource('vatrates', 'REST\VatRatesController');
});
