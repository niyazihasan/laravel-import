<?php

use Illuminate\Support\Facades\Route;

Route::get('/sale', 'App\Http\Controllers\SaleController@sale')->name('sale.index');
Route::post('/import', 'App\Http\Controllers\SaleController@import')->name('sale.import');
