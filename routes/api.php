<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('register', 'API\UserController@register');
Route::post('login', 'API\UserController@login');
Route::get('users', 'API\UserController@getAuthenticatedUser')->middleware('jwt.verify');


Route::prefix('product')->group(function () {
    Route::get('/', 'API\ProductController@index')->name('product.index');
    Route::get('/get/{id_product}', 'API\ProductController@product')->name('product.product');
    Route::get('/get', 'API\ProductController@myProduct')->name('product.myProduct');
    Route::post('/add', 'API\ProductController@add')->name('product.add');
    Route::post('/edit/{product}', 'API\ProductController@edit')->name('product.edit');
    Route::delete('/{product}', 'API\ProductController@delete')->name('product.delete');
});



Route::prefix('orders')->group(function () {
    Route::get('/', 'API\OrdersController@index');
    Route::get('/paid', 'API\OrdersController@ordersPaid');
    Route::get('/proses', 'API\OrdersController@paid');
    Route::get('/{id_orders}', 'API\OrdersController@orders');
    Route::post('/', 'API\OrdersController@insert');
    Route::delete('/{id_orders}', 'API\OrdersController@remove');
    Route::delete('/unlist/{id_product}', 'API\OrdersController@removeProduct');
});