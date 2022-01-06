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

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('activities/{type}/{id}', 'ActivitiesController@index');
Route::get('activityDetail/{id}', 'ActivitiesController@detail');
Route::get('activityDetailShow/{id}', 'ActivitiesController@detailShow');
Route::get('noticeDetail/{id}', 'ActivitiesController@noticeDetail');
Route::get('kfProblemDetail', 'ActivitiesController@kfProblemDetail');
Route::get('monitor', 'Controller@monitor');
