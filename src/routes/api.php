<?php


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


Route::group(['middleware' => ['auth:api', 'tenant']], function() {

	Route::prefix('api/v1')->group(function () {

		Route::post('contacts/deactivate', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@deactivate');
		Route::post('contacts/activate', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@activate');
		//Route::any('contacts/{id}/statement', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@statement');
		//Route::get('contacts/{id}/sales', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@sales');
		//Route::get('contacts/{id}/purchases', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@purchases');
		Route::get('contacts/{id}/remarks', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@remarks');
		//Route::get('contacts/{id}/mails', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@mails');
		Route::get('contacts/{id}/comments', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController@comments');

		Route::resource('contacts', 'Rutatiina\Contact\Http\Controllers\Api\V1\ContactController', ['as' => 'api.v1']);

	});


});

