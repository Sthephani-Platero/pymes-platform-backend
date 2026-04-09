<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Http\Controllers\PulsarController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\TrendsController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\InnovationController;
use App\Http\Controllers\DashboardController;



Route::get('/dashboard', [DashboardController::class, 'index']);


Route::prefix('intelligence')->group(function () {
    Route::get('/market', [MarketController::class, 'index']);
    Route::get('/trends', [TrendsController::class, 'index']);
    Route::get('/predictions', [PredictionController::class, 'index']);
    Route::get('/innovation', [InnovationController::class, 'index']);
   
});
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
    //Route::get('/dashboard', function (Request $request) {
    //    return response()->json([
    //        'message' => 'Bienvenida al dashboard 🔥',
    //        'user' => $request->user()
    //    ]);
    //});

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

// ==========================
// 🌐 Pulsar API Routes
// ==========================
//
Route::prefix('pulsar')->group(function () {

    Route::get('/brands-profiles', [PulsarController::class, 'getBrandsProfiles']);

    Route::get('/engagements', [PulsarController::class, 'getEngagements']);

    Route::get('/comments', [PulsarController::class, 'getComments']);

    Route::get('/mentions-trend', [PulsarController::class, 'getMentionsTrend']);

});

Route::get('/pulsar/impressions', [PulsarController::class, 'getImpressions']);



