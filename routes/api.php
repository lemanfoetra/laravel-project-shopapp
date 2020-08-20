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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register', 'API\UserController@register');
Route::post('login', 'API\UserController@login');
Route::get('book', 'API\BookController@book');

Route::get('bookall', 'API\BookController@bookAuth')->middleware('jwt.verify');
Route::get('user', 'API\UserController@getAuthenticatedUser')->middleware('jwt.verify');


Route::prefix('product')->group(function () {
    Route::get('/', 'API\ProductController@index')->name('product.index');
    Route::post('/add', 'API\ProductController@add')->name('product.add');
    Route::post('/edit/{product}', 'API\ProductController@edit')->name('product.edit');
});