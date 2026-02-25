<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Registro empresa
Route::post('/register-company', [RegisterCompanyController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});


// Ruta de ejemplo para el dashboard
Route::middleware('auth:sanctum')->get('/dashboard', function (Request $request) {
    return response()->json([
        'message' => 'Bienvenida al dashboard 🔥',
        'user' => $request->user()
    ]);
});