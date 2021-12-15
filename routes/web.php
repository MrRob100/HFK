<?php

use App\Http\Controllers\ChartController;
use App\Http\Controllers\FindPairsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\PairsController;
use App\Http\Controllers\ResultsController;
use Illuminate\Support\Facades\Route;

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

/* chart data */
Route::get('/chart_data', [ChartController::class, 'data'])->name('chart.data');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/results', [ResultsController::class, 'index'])->name('get_results');

/* pairs */
Route::get('/pairs', [PairsController::class, 'index']);
Route::post('pairs', [PairsController::class, 'create']);

Route::get('sync', [PairsController::class, 'sync']);


/* create an input */
//Route::post('/input', [InputController::class, 'create']);

/* position */
Route::get('/position', [ManualController::class, 'position']);

/* transfer */
//Route::get('/transfer', [ManualController::class, 'transfer']);

/* delete */
//Route::post('/pairs/delete', [PairsController::class, 'delete']);
