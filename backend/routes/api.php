<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Autenticación basada en Bearer Token con Sanctum
| NO usamos cookies
| NO usamos CSRF
*/

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// ==========================
// 🔓 Rutas Públicas
// ==========================

// Registro empresa
Route::post('/register-company', [RegisterCompanyController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);


// ==========================
// 🔒 Rutas Protegidas
// ==========================

Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Usuario autenticado
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // Dashboard
    Route::get('/dashboard', function (Request $request) {
        return response()->json([
            'message' => 'Bienvenida al dashboard 🔥',
            'user' => $request->user()
        ]);
    });

});