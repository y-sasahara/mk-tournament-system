<?php

use App\Http\Controllers\InstantGroupingController;
use App\Http\Controllers\SnakeGroupingController;
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

Route::resource('instant-grouping', InstantGroupingController::class, ['only' => ['index', 'create', 'store', 'show']]);
Route::resource('snake-grouping', SnakeGroupingController::class, ['only' => ['index', 'create', 'store', 'show']]);
