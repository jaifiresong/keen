<?php
Route::any('/')->to('App\Demo\Controllers\DefaultController@index')->middleware(['SayHello', 'SayBye']);


Route::group(['prefix' => '/test', 'middleware' => ['SayHello', 'SayBye']])->add(
    Route::get('t1')->to(function () {
        return 't2';
    }),
    Route::post('t2')->to(function () {
        return 't2';
    })->middleware(['SayHello', 'SayBye'])
);

Route::group(['to' => 'App\Admin\Controllers\DefaultController@index'])->add(
    Route::get('/index'),
    Route::get('/index/index')
);