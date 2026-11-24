<?php

use App\Http\Controllers\Api\PecaController;
use Illuminate\Support\Facades\Route;

Route::get('/locais', [PecaController::class, 'locais']);
Route::post('/pecas', [PecaController::class, 'store']);
Route::get('/pecas/{id}', [PecaController::class, 'show']);
Route::put('/pecas/{id}', [PecaController::class, 'update']);
