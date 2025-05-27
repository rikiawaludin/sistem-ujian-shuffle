<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UjianSoalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rute untuk mengambil soal ujian (nantinya akan diacak oleh Express.js)
Route::middleware('auth:sanctum')->get('/ujian/{id_ujian}/ambil-soal', [UjianSoalController::class, 'getSoalUntukUjian'])->name('api.ujian.ambilsoal');
