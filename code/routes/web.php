<?php

Auth::routes();
Route::get('autologin/{token}', 'Auth\LoginController@autologin')->name('autologin');

Route::get('ordini.xml', 'OrdersController@rss')->name('rss');
Route::get('ordini.ics', 'OrdersController@ical')->name('ical');

Route::get('gas/{id}/logo', 'GasController@getLogo');

Route::get('payment/status/satispay', 'PaymentController@statusPaymentSatispay')->name('payment.status_satispay');

Route::post('mail/status/aws', 'MailController@postStatusSES');
Route::post('mail/status/sib', 'MailController@postStatusSendinblue');

Route::middleware(['auth'])->group(function() {
    Route::get('users/blocked', 'UsersController@blocked')->name('users.blocked');

    Route::middleware(['commonuser'])->group(function() {
        Route::get('/', function () {
            Session::reflash();
            return Redirect::to('/dashboard');
        })->name('root');

        Route::get('/home', function () {
            return Redirect::to('/dashboard');
        });

        Route::get('dashboard', 'CommonsController@getIndex')->name('dashboard');
        Route::post('dashboard/verify', 'CommonsController@postVerify');

        Route::get('gas/{id}/header', 'GasController@objhead')->name('gas.objhead');
        Route::get('gas/dumpdb', 'GasController@databaseDump')->name('gas.dumpdb');

        Route::get('users/ro/{id}', 'UsersController@show_ro');
        Route::get('users/{id}/header', 'UsersController@objhead')->name('users.objhead');
        Route::get('users/search', 'UsersController@search')->name('users.search');
        Route::get('users/searchorders/{id}', 'UsersController@searchOrders')->name('users.orders');
        Route::get('users/profile', 'UsersController@profile')->name('profile');
        Route::get('users/picture/{id}', 'UsersController@picture');
        Route::get('users/export', 'UsersController@export');
        Route::post('users/notifications/{id}', 'UsersController@notifications')->name('users.notifications');
        Route::get('users/fees', 'UsersController@fees')->name('users.fees');
        Route::get('users/fee/{id}', 'UsersController@feeRow')->name('users.fee');
        Route::post('users/fees', 'UsersController@feesSave')->name('users.savefees');
        Route::get('users/password', 'UsersController@changePassword')->name('users.password');
        Route::post('users/revisioned/{id}', 'UsersController@revisioned')->name('users.revisioned');
        Route::post('users/promote/{id}', 'UsersController@promote')->name('users.promote');
        Route::post('users/reassign/{id}', 'UsersController@reassign')->name('users.reassign');
        Route::get('users/{id}/bookings', 'UsersController@bookings')->name('users.bookings');
        Route::get('users/{id}/stats', 'UsersController@statistics')->name('users.stats');
        Route::get('users/{id}/accounting', 'UsersController@accounting')->name('users.accounting');
        Route::get('users/{id}/friends', 'UsersController@friends')->name('users.friends');
        Route::get('users/tour/start', 'UsersController@startTour')->name('users.tour.start');
        Route::get('users/tour/finish', 'UsersController@finishTour')->name('users.tour.finish');

        Route::get('friends/{id}/header', 'FriendsController@objhead')->name('friends.objhead');

        Route::get('roles/{id}/header', 'RolesController@objhead')->name('roles.objhead');
        Route::get('roles/user/table/{user_id}', 'RolesController@tableByUser')->name('roles.usertable');
        Route::get('roles/user/{user_id}', 'RolesController@formByUser');
        Route::get('roles/supplier/table/{supplier_id}', 'RolesController@tableBySupplier')->name('roles.suppliertable');
        Route::get('roles/supplier/{supplier_id}', 'RolesController@formBySupplier');
        Route::post('roles/attach', 'RolesController@attach');
        Route::post('roles/detach', 'RolesController@detach');

        Route::get('suppliers/ro/{id}', 'SuppliersController@show_ro');
        Route::get('suppliers/{id}/header', 'SuppliersController@objhead')->name('suppliers.objhead');
        Route::get('suppliers/catalogue/{id}/{format?}', 'SuppliersController@catalogue')->name('suppliers.catalogue');
        Route::get('suppliers/{id}/products', 'SuppliersController@productsDetails');
        Route::get('suppliers/{id}/products_grid', 'SuppliersController@productsGrid');
        Route::get('suppliers/{id}/invoicedata', 'SuppliersController@invoiceData')->name('suppliers.invoicedata');

        Route::get('products/ro/{id}', 'ProductsController@show_ro');
        Route::get('products/duplicate/{id}', 'ProductsController@duplicate')->name('products.duplicate');
        Route::get('products/{id}/header', 'ProductsController@objhead')->name('products.objhead');
        Route::post('products/massiveupdate', 'ProductsController@massiveUpdate');
        Route::get('products/picture/{id}', 'ProductsController@picture');
        Route::get('products/{id}/post_feedback', 'ProductsController@postFeedback')->name('products.feedback');
        Route::get('products/{id}/ask_update_price', 'ProductsController@askUpdatePrices')->name('products.askupdateprices');
        Route::post('products/{id}/update_price', 'ProductsController@updatePrices')->name('products.updateprices');
        Route::get('products/search', 'ProductsController@search')->name('products.search');
        Route::get('products/price', 'ProductsController@price')->name('products.price');

        Route::get('vatrates/{id}/header', 'VatRatesController@objhead')->name('vatrates.objhead');

        Route::get('invoices', 'InvoicesController@index')->name('invoices.index');
        Route::get('invoices/{id}/products', 'InvoicesController@products')->name('invoices.products');
        Route::get('invoices/{id}/orders', 'InvoicesController@orders')->name('invoices.orders');
        Route::get('invoices/{id}/movements', 'InvoicesController@getMovements')->name('invoices.movements');
        Route::post('invoices/{id}/movements', 'InvoicesController@postMovements')->name('invoices.savemovements');
        Route::post('invoices/wire/{step}/{id}', 'InvoicesController@wiring');
        Route::get('invoices/search', 'InvoicesController@search')->name('invoices.search');
        Route::get('invoices/{id}/header', 'InvoicesController@objhead');

		Route::get('receipts', 'ReceiptsController@index')->name('receipts.index');
		Route::get('receipts/search', 'ReceiptsController@search')->name('receipts.search');
        Route::get('receipts/{id}/header', 'ReceiptsController@objhead')->name('receipts.objhead');
        Route::get('receipts/{id}/handle', 'ReceiptsController@handle')->name('receipts.handle');
        Route::get('receipts/{id}/download', 'ReceiptsController@download')->name('receipts.download');

        Route::get('categories/{id}/header', 'CategoriesController@objhead')->name('categories.objhead');

        Route::get('measures/{id}/header', 'MeasuresController@objhead')->name('measures.objhead');
        Route::get('measures/discretes', 'MeasuresController@discretes');

        Route::get('variants/{id}/matrix', 'VariantsController@matrix')->name('variants.matrix');
        Route::post('variants/{id}/matrix', 'VariantsController@updateMatrix')->name('variants.updatematrix');
        Route::get('variants/{id}/header', 'VariantsController@objhead')->name('variants.objhead');

        Route::get('orders/{id}/header', 'OrdersController@objhead')->name('orders.objhead');
        Route::get('orders/search', 'OrdersController@search');
        Route::get('orders/fixes/{id}/{product_id}', 'OrdersController@getFixes');
        Route::post('orders/fixes/{id}', 'OrdersController@postFixes');
        Route::get('orders/fixmods/{id}', 'OrdersController@getFixModifiers')->name('orders.fixmodifiers');
        Route::post('orders/fixmods/{id}', 'OrdersController@postFixModifiers')->name('orders.postfixmodifiers');
        Route::get('orders/export/{id}/{type}', 'OrdersController@exportModal')->name('orders.export');
        Route::get('orders/document/{id}/{type}', 'OrdersController@document')->name('orders.document');

        Route::get('modtype/search', 'ModifierTypesController@search')->name('modtype.search');

        Route::get('modifiers/strings/{target}', 'ModifiersController@strings')->name('modifiers.string');
        Route::get('modifiers/{id}/post_feedback', 'ModifiersController@postFeedback')->name('modifiers.feedback');
        Route::get('modifiers/{id}/fix_order_attach', 'ModifiersController@getFixOrderAttach')->name('modifiers.fixorderattach');
        Route::post('modifiers/{id}/fix_order_attach', 'ModifiersController@postFixOrderAttach')->name('modifiers.postfixorderattach');

        Route::get('dates/query', 'DatesController@query');
        Route::get('dates/{id}/header', 'DatesController@objhead')->name('dates.objhead');
        Route::get('dates/orders', 'DatesController@orders')->name('dates.orders');
        Route::post('dates/orders', 'DatesController@updateOrders')->name('dates.updateorders');

        Route::get('aggregates/{id}/header', 'AggregatesController@objhead')->name('aggregates.objhead');
        Route::post('aggregates/notify/{id}', 'AggregatesController@notify');
        Route::get('aggregates/export/{id}/{type}', 'AggregatesController@exportModal')->name('aggregates.export');
        Route::get('aggregates/document/{id}/{type}', 'AggregatesController@document')->name('aggregates.document');
        Route::get('aggregates/{id}/post_feedback', 'AggregatesController@postFeedback')->name('aggregates.feedback');
        Route::get('aggregates/{id}/multigas', 'AggregatesController@multiGAS')->name('aggregates.multigas');

        Route::get('attachments/{id}/header', 'AttachmentsController@objhead')->name('attachments.objhead');
        Route::get('attachments/download/{id}', 'AttachmentsController@download');

        Route::get('booking/{aggregate_id}/user/{user_id}/dynamics', 'BookingUserController@dynamicModifiers')->name('booking.dynamics');
        Route::get('bookings/{id}/header', 'BookingController@objhead')->name('bookings.objhead');
        Route::get('booking/{aggregate_id}/user/{user_id}/header', 'BookingUserController@objhead2');
        Route::get('booking/{aggregate_id}/user/{user_id}/document', 'BookingUserController@document');
        Route::get('booking/{id}/header', 'BookingController@objhead')->name('booking.objhead');

        Route::get('deliveries/{id}/header', 'DeliveriesController@objhead')->name('deliveries.objhead');
        Route::get('delivery/{aggregate_id}/user/{user_id}/header', 'DeliveryUserController@objhead2');
        Route::get('deliveries/{aggregate_id}/fast', 'DeliveryUserController@getFastShipping');
        Route::post('deliveries/{aggregate_id}/fast', 'DeliveryUserController@postFastShipping');

        Route::get('notifications/{id}/header', 'NotificationsController@objhead')->name('notifications.objhead');
        Route::get('notificatios/search', 'NotificationsController@search')->name('notifications.search');
        Route::post('notifications/markread/{id}', 'NotificationsController@markread');

        Route::post('multigas/attach', 'MultiGasController@attach');
        Route::post('multigas/detach', 'MultiGasController@detach');
        Route::get('multigas/{id}/goto', 'MultiGasController@goTo')->name('multigas.goto');
        Route::get('multigas/{id}/header', 'GasController@objhead')->name('multigas.objhead');

        Route::post('payment/do', 'PaymentController@doPayment')->name('payment.do');

        Route::get('movements/ro/{id}', 'MovementsController@show_ro');
        Route::get('movements/{id}/header', 'MovementsController@objhead')->name('movements.objhead');
        Route::get('movtypes/{id}/header', 'MovementTypesController@objhead')->name('movtypes.objhead');
        Route::get('movements/credits/{type}', 'MovementsController@creditsTable')->name('movements.credits');
        Route::get('movements/{targetid}/history', 'MovementsController@getHistory')->name('movements.history');
		Route::get('movements/history/details', 'MovementsController@getHistoryDetails')->name('movements.history.details');
        Route::get('movements/{targetid}/balance', 'MovementsController@getBalance')->name('movements.balance');
        Route::post('movements/recalculate', 'MovementsController@recalculate');
        Route::post('movements/close', 'MovementsController@closeBalance');
        Route::get('movements/askdelete/{id}', 'MovementsController@askDelete')->name('movements.askdelete');
		Route::get('movements/askdeletebalance/{id}', 'MovementsController@askDeleteBalance')->name('movements.askdeletebalance');
        Route::post('movements/deletebalance/{id}', 'MovementsController@deleteBalance')->name('movements.deletebalance');
        Route::get('movements/document/{type}/{subtype?}', 'MovementsController@document');

		Route::get('movtypes/{id}/post_feedback', 'MovementTypesController@postFeedback')->name('movtypes.feedback');
		Route::get('movtypes/{id}/brokenmodifier', 'MovementTypesController@brokenModifier')->name('movtypes.notifybrokenmodifier');

        Route::get('import/esmodal', 'ImportController@esModal')->name('import.esmodal');
        Route::post('import/csv', 'ImportController@postCsv');
        Route::get('import/gdxp', 'ImportController@getGdxp');
        Route::post('import/gdxp', 'ImportController@postGdxp');

        Route::resource('gas', 'GasController');
        Route::resource('multigas', 'MultiGasController');
        Route::resource('users', 'UsersController');
        Route::resource('friends', 'FriendsController');
        Route::resource('roles', 'RolesController');
        Route::resource('suppliers', 'SuppliersController');
        Route::resource('products', 'ProductsController');
        Route::resource('vatrates', 'VatRatesController');
        Route::resource('invoices', 'InvoicesController');
        Route::resource('receipts', 'ReceiptsController');
        Route::resource('deliveries', 'DeliveriesController');
        Route::resource('categories', 'CategoriesController');
        Route::resource('measures', 'MeasuresController');
        Route::resource('variants', 'VariantsController');
        Route::resource('orders', 'OrdersController');
        Route::resource('dates', 'DatesController');
        Route::resource('aggregates', 'AggregatesController');
        Route::resource('attachments', 'AttachmentsController');
        Route::resource('booking.user', 'BookingUserController');
        Route::resource('delivery.user', 'DeliveryUserController');
        Route::resource('booking', 'BookingController');
        Route::resource('bookings', 'BookingController');
        Route::resource('notifications', 'NotificationsController');
        Route::resource('movements', 'MovementsController');
        Route::resource('movtypes', 'MovementTypesController');
        Route::resource('modtypes', 'ModifierTypesController');
        Route::resource('modifiers', 'ModifiersController');
        Route::resource('stats', 'StatisticsController');
    });
});
