<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//licence
Route::get('/licence', 'LoginController@licence')->name('licence');
Route::get('/under-construction', 'LoginController@underConstruction')->name('under-construction');

Route::group(['middleware' => 'is.guest'], function() {
    Route::get('/', 'LoginController@publicHome')->name('public-home');
    //user validity expired
    Route::get('/user/expired', 'LoginController@userExpired')->name('user-expired');

    Route::get('/login', 'LoginController@login')->name('login');
    Route::post('/login/action', 'LoginController@loginAction')->name('login-action');
});

Route::group(['middleware' => 'auth.check'], function () {
    //common routes
    Route::get('/dashboard', 'LoginController@dashboard')->name('dashboard');
    Route::get('/my/profile', 'UserController@profileView')->name('user-profile');
    Route::get('/lockscreen', 'LoginController@lockscreen')->name('lockscreen');
    Route::get('/logout', 'LoginController@logout')->name('logout-action');

    //superadmin routes
    Route::group(['middleware' => ['user.role:0,,']], function () {
        Route::get('/user/register', 'UserController@register')->name('user-register');
        Route::post('/user/register/action', 'UserController@registerAction')->name('user-register-action');
        Route::get('/user/list', 'UserController@userList')->name('user-list');
    });

    //admin routes
    Route::group(['middleware' => ['user.role:1,,']], function () {
        //edit
        //account
        Route::get('/account/edit', 'AccountController@edit')->name('account-edit');
        Route::post('/account/updation/action', 'AccountController@updationAction')->name('account-updation-action');
        //employee
        Route::get('/hr/employee/edit', 'EmployeeController@edit')->name('employee-edit');
        Route::post('/hr/employee/updation/action', 'EmployeeController@updationAction')->name('employee-updation-action');
    });

    //user routes
    Route::group(['middleware' => ['user.role:0,1,2']], function () {
        //account
        Route::get('/account/register', 'AccountController@register')->name('account-register');
        Route::post('/account/register/action', 'AccountController@registerAction')->name('account-register-action');
        Route::get('/account/list', 'AccountController@list')->name('account-list');

        //staff
        Route::get('/hr/employee/register', 'EmployeeController@register')->name('employee-register');
        Route::post('/hr/employee/register/action', 'EmployeeController@registerAction')->name('employee-register-action');
        Route::get('/hr/employee/list', 'EmployeeController@list')->name('employee-list');
        //Route::get('/employee/get/account/{id}', 'EmployeeController@getEmployeeByaccountId')->name('employee-get-by-account-id');
        //Route::get('/employee/get/employee/{id}', 'EmployeeController@getEmployeeByEmployeeId')->name('employee-get-by-employee-id');

        //sales
        Route::get('/sale/register', 'SaleController@register')->name('sale-register');
        Route::post('/sale/register/action', 'SaleController@registerAction')->name('sale-register-action');
        Route::get('/sale/bill/print/{id}', 'SaleController@saleBillPrint')->name('sale-bill-print');
        Route::get('/sale/list', 'SaleController@list')->name('sale-list');
        Route::post('/sale/detail/add', 'SaleController@addSaleDetail')->name('sale-deatail-add');
        Route::post('/sale/detail/delete', 'SaleController@deleteSaleDetail')->name('sale-deatail-delete');
        Route::get('/sale/view/invoice/{id}', 'SaleController@viewInvoice')->name('sale-invoice');
        Route::get('/sale/detail/by/account/{id}', 'SaleController@getSaleDetailByAccountId')->name('sale-detail-by-accountId');

        //purchases
        Route::get('/purchase/register', 'PurchaseController@register')->name('purchase-register');
        Route::post('/purchase/register/action', 'PurchaseController@registerAction')->name('purchase-register-action');
        Route::get('/purchase/list', 'PurchaseController@list')->name('purchase-list');
        Route::post('/purchase/detail/add', 'PurchaseController@addPurchaseDetail')->name('purchase-deatail-add');
        Route::post('/purchase/detail/delete', 'PurchaseController@deletePurchaseDetail')->name('purchase-deatail-delete');
        Route::get('/purchase/view/invoice/{id}', 'PurchaseController@viewInvoice')->name('purchase-invoice');

        //product category
        Route::get('/product-category/register', 'ProductCategoryController@register')->name('product-category-register');
        Route::post('/product-category/register/action', 'ProductCategoryController@registerAction')->name('product-category-register-action');
        Route::get('/product-category/list', 'ProductCategoryController@list')->name('product-category-list');
        Route::get('/product-selection/list', 'ProductCategoryController@selectionList')->name('product-selection-list');

        //product category
        Route::get('/product/register', 'ProductController@register')->name('product-register');
        Route::post('/product/register/action', 'ProductController@registerAction')->name('product-register-action');
        Route::get('/product/list', 'ProductController@list')->name('product-list');

        //vouchers
        Route::get('/voucher/register', 'VoucherController@register')->name('voucher-register');
        Route::post('/voucher/register/action', 'VoucherController@registerAction')->name('voucher-register-action');
        Route::get('/voucher/list/cash', 'VoucherController@list')->name('voucher-list');

        //final statement
        Route::get('/statement/account-statement', 'AccountController@satement')->name('account-statement');
        Route::get('/statement/sale-statement', 'SalesController@statement')->name('sale-statement');
    });
});