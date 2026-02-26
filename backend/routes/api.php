<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

// Forgot Password
Route::post('/forgot-password', function (Request $request) {
    $request->validate([
        'email' => 'required|email'
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Reset link sent'])
        : response()->json(['message' => 'Unable to send link'], 400);
});

// Reset Password
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Password reset successful'])
        : response()->json(['message' => 'Invalid or expired token'], 400);
});

});