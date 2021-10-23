<?php

use Illuminate\Support\Facades\Route;

#general routes that apply to any app **********************************************************************************
Route::group(['middleware' => ['web', 'auth', 'tenant']], function() {

    Route::post('contacts/search', 'Rutatiina\Contact\Http\Controllers\ContactController@search')->name('contacts.search');
    Route::post('contacts/search/salespersons', 'Rutatiina\Contact\Http\Controllers\ContactController@searchSalesPersons')->name('contacts.search.salespersons');
    Route::any('contacts/datatables', 'Rutatiina\Contact\Http\Controllers\ContactController@datatables')->name('contacts.datatables');
    Route::post('contacts/import', 'Rutatiina\Contact\Http\Controllers\ContactController@import')->name('contacts.import');
    Route::patch('contacts/deactivate', 'Rutatiina\Contact\Http\Controllers\ContactController@deactivate')->name('contacts.deactivate');
    Route::post('contacts/delete', 'Rutatiina\Contact\Http\Controllers\ContactController@delete')->name('contacts.delete');
    Route::patch('contacts/activate', 'Rutatiina\Contact\Http\Controllers\ContactController@activate')->name('contacts.activate');
    Route::any('contacts/{id}/statement', 'Rutatiina\Contact\Http\Controllers\ContactController@statement')->name('contacts.statement');
    Route::get('contacts/{id}/sales', 'Rutatiina\Contact\Http\Controllers\ContactController@sales')->name('contacts.sales');
    Route::get('contacts/{id}/purchases', 'Rutatiina\Contact\Http\Controllers\ContactController@purchases')->name('contacts.purchases');
    Route::get('contacts/{id}/remarks', 'Rutatiina\Contact\Http\Controllers\ContactController@remarks')->name('contacts.remarks');
    Route::get('contacts/{id}/mails', 'Rutatiina\Contact\Http\Controllers\ContactController@mails')->name('contacts.mails');
    Route::any('contacts/{id}/comments', 'Rutatiina\Contact\Http\Controllers\ContactController@comments')->name('contacts.comments');

    Route::resource('contacts', 'Rutatiina\Contact\Http\Controllers\ContactController');

});
