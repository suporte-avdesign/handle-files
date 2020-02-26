<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('files', 'FilesInventoryController@index')->name('files');
Route::get('create/file', 'FilesInventoryController@create')->name('create-file');


