<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


    Route::post('login', 'Api\AuthController@login');
    Route::post('register', 'Api\AuthController@register');
  
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'Api\AuthController@logout');
        Route::get('user', 'Api\AuthController@user');
        
        Route::group(['prefix' => 'employee'], function () {
            Route::get('/', 'Api\EmployeeController@index');
            Route::post('/', 'Api\EmployeeController@store');
            Route::put('/{id}', 'Api\EmployeeController@update');
            Route::delete('/{id}', 'Api\EmployeeController@destroy');
            Route::get('/age/{x}', 'Api\EmployeeController@filter');
        });
    });

    Route::group(['prefix' => 'password'], function () {
        Route::post('/create', 'Api\PasswordResetController@create');
        Route::get('/find/{token}', 'Api\PasswordResetController@find');
        Route::post('/reset', 'Api\PasswordResetController@reset');
    });
