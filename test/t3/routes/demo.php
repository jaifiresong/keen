<?php

Route::any('/')->to('App\Demo\Controllers\DefaultController@index')->middleware(['SayHello', 'SayBye']);
Route::any('/login')->to('Common\LoginController@login');

Route::group(['prefix' => '/test', 'middleware' => ['SayHello', 'SayBye']])->add(
    Route::get('t1')->to(function () {
        return 't1为get请求';
    })->middleware(['SayHello', 'SayBye']),
    Route::post('t2')->to(function () {
        return 't2为post请求';
    })
);

Route::group(['to' => 'App\Demo\Controllers\DefaultController@index'])->add(
    Route::get('/index'),
    Route::get('/index/index')
);