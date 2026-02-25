<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisterCompanyController;

// Ruta health
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
// Ruta registro empresa + owner
Route::post('/register-company', [RegisterCompanyController::class, 'register']);

// Ruta login
Route::post('/login', function (Request $request) {

    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Credenciales incorrectas'
        ], 401);
    }

    $request->session()->regenerate();

    return response()->json([
        'message' => 'Login exitoso',
        'user' => Auth::user()
    ]);
});

// 🔐 Ruta protegida
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');