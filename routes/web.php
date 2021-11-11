<?php

use App\Http\Controllers\ChartController;
use App\Http\Controllers\FindPairsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessagesController;
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

Route::get('/', function () {
    return view('welcome');
});

/* chart data */
Route::get('/chart', [ChartController::class, 'data'])->name('chart.data');

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/find_pairs', [FindPairsController::class, 'findPairs'])->name('find_pairs');

Route::get('/latest_message', [MessagesController::class, 'getLatest'])->name('get_latest');
