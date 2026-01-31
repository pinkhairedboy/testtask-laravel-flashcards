<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens/create', [AuthController::class, 'login']);
