<?php

use Illuminate\Support\Facades\Route;
use Rutatiina\Contact\Http\Controllers\ContactController;

#general routes that apply to any app **********************************************************************************
Route::group(['middleware' => ['web', 'auth', 'tenant']], function() {

    Route::prefix('contacts')->group(function ()
    {
        Route::post('routes', [ContactController::class, 'routes'])->name('contacts.routes');
        Route::post('search', 'Rutatiina\Contact\Http\Controllers\ContactController@search')->name('contacts.search');
        Route::post('search/salespersons', 'Rutatiina\Contact\Http\Controllers\ContactController@searchSalesPersons')->name('contacts.search.salespersons');
        Route::any('datatables', 'Rutatiina\Contact\Http\Controllers\ContactController@datatables')->name('contacts.datatables');
        Route::post('import', 'Rutatiina\Contact\Http\Controllers\ContactController@import')->name('contacts.import');
        Route::patch('deactivate', 'Rutatiina\Contact\Http\Controllers\ContactController@deactivate')->name('contacts.deactivate');
        Route::post('delete', 'Rutatiina\Contact\Http\Controllers\ContactController@delete')->name('contacts.delete');
        Route::patch('activate', 'Rutatiina\Contact\Http\Controllers\ContactController@activate')->name('contacts.activate');
        Route::any('{id}/statement', 'Rutatiina\Contact\Http\Controllers\ContactController@statement')->name('contacts.statement');
        Route::get('{id}/sales', 'Rutatiina\Contact\Http\Controllers\ContactController@sales')->name('contacts.sales');
        Route::get('{id}/purchases', 'Rutatiina\Contact\Http\Controllers\ContactController@purchases')->name('contacts.purchases');
        Route::get('{id}/remarks', 'Rutatiina\Contact\Http\Controllers\ContactController@remarks')->name('contacts.remarks');
        Route::get('{id}/mails', 'Rutatiina\Contact\Http\Controllers\ContactController@mails')->name('contacts.mails');
        Route::any('{id}/comments', 'Rutatiina\Contact\Http\Controllers\ContactController@comments')->name('contacts.comments');
    });

    Route::resource('contacts', 'Rutatiina\Contact\Http\Controllers\ContactController');

});
