<?php

use App\Http\Controllers\workstationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ws', [workstationController::class, 'getWS']);
Route::get('/ws_turno', [workstationController::class, 'getWsTurno']);
Route::post('/ws_turno_post', [workstationController::class, 'postWsTurno']);
Route::post('/ws_turno_add_post', [workstationController::class, 'postWsTurnoAdd']);
Route::post('/ws_post', [workstationController::class, 'postWs']);

Route::get('/getWsTurno/{id}', [workstationController::class, 'getWsTurnoBy']);


Route::get('/getVend', [workstationController::class, 'getVend']);
Route::get('/getDep', [workstationController::class, 'getDep']);
