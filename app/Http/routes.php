<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    // Authentication routes...
    Route::get('/signin', 'Auth\AuthController@getLogin')->name('login');
    Route::post('/signin', 'Auth\AuthController@postLogin')->name('checkLogin');
    Route::get('/logout', 'Auth\AuthController@getLogout')->name('logout');

    Route::get('/fb/signin', 'Auth\AuthController@redirectToProvider')->name('fbRegister');
    Route::get('/fb/signin/callback', 'Auth\AuthController@handleProviderCallback')
        ->name('fbRegisterCheck');

    // Registration routes...
    Route::get('/signup', 'Auth\AuthController@getRegister')->name('register');
    Route::post('/signup', 'Auth\AuthController@postRegister')->name('checkRegister');

    // Games routes
    Route::get('/', 'GameController@index')->name('home');
    Route::get('game/{id}', 'GameController@one')->where('id', '[0-9]+');
    Route::group(['middleware' => ['auth', 'active']], function () {
        Route::put('game/{id}', 'GameController@update')->where('id', '[0-9]+');
        Route::delete('game/{id}', 'GameController@delete')->where('id', '[0-9]+');
        Route::get('game/{id}/delete', 'GameController@delete')->where('id', '[0-9]+');
        Route::get('game/{id}/complain', 'ComplaintController@create')->where('id', '[0-9]+');
    });
    Route::group(['middleware' => ['auth']], function () {
        Route::post('game', 'GameController@create');
    });


    // Users routes
    Route::get('/user/search', 'UserController@search');
    Route::get('/user/role', 'UserController@role');
    Route::get('/user/me', [
        'middleware' => ['auth'],
        'uses' => 'UserController@one',
    ]);
    Route::post('/user/{id}/restore', [
        'middleware' => ['auth'],
        'uses' => 'UserController@restore',
    ]);
    Route::get('/users', 'UserController@index');
    Route::group(['middleware' => ['auth', 'active']], function () {
        Route::put('/user/{id}', 'UserController@update')->where('id', '[0-9]+');
        Route::post('/user/{id}/avatar', 'UserController@updateAvatar')->where('id', '[0-9]+');
        Route::delete('/user/{id}', 'UserController@delete')->where('id', '[0-9]+');
    });

    // Chart routes
    Route::get('/chart', 'ChartController@getIndex')->name('chart');
});
