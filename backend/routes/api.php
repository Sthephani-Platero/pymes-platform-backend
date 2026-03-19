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


Route::prefix('intelligence')->group(function () {
    Route::get('/market', [MarketController::class, 'index']);
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



// ==========================
// 🌐 Test Pulsar API
// ==========================
//
//Route::get('/test-pulsar', function () {
//
//    $query = <<<'GRAPHQL'
//    query BrandsPlusProfiles($page: Int, $limit: Int) {
//      brands(page: $page, limit: $limit) {
//        total
//        nextPage
//        brands {
//          id
//          name
//          profiles {
//            id
//            source
//            name
//            plugged
//          }
//        }
//      }
//    }
//    GRAPHQL;
//
//    // Definir las variables de la query
//    $variables = [
//        'page' => 2,
//        'limit' => 10
//    ];
//
//    $response = Http::withHeaders([
//        'Authorization' => 'Bearer ' . env('PULSAR_API_KEY'),
//        'Content-Type' => 'application/json'
//    ])->post('https://data.pulsarplatform.com/graphql/core', [
//        'query' => $query,
//        'variables' => $variables
//    ]);
//
//    return response()->json($response->json());
//});
//
//
//Route::get('/test-engagements', function () {
//
//    // Ejemplo de IDs (cámbialos por los reales que tengas)
//    $brandID = 8223; // id de la marca
//    $profID1 = 18031;
//    $profID2 = 42773;
//    $profID3 = 54568;
//
//    // Query GraphQL
//    $query = <<<'GRAPHQL'
//    query Engagements($filter: Filter!, $metric: ContentMetric) {
//        engagements(filter: $filter, metric: $metric)
//    }
//    GRAPHQL;
//
//    // Variables
//    $variables = [
//        'filter' => [
//            'dateFrom' => '2025-10-11T00:00:00Z',
//            'dateTo'   => '2025-11-11T23:59:59Z',
//            'brandId'  => $brandID,
//            'profiles' => [$profID1, $profID2, $profID3],
//        ],
//        'metric' => 'SUM'
//    ];
//
//    // Llamada a Pulsar
//    $response = Http::withHeaders([
//        'Authorization' => 'Bearer ' . env('PULSAR_API_KEY'),
//        'Content-Type'  => 'application/json'
//    ])->post('https://data.pulsarplatform.com/graphql/core', [
//        'query'     => $query,
//        'variables' => $variables
//    ]);
//
//    return response()->json($response->json());
//});
//
//Route::get('/test-comments', function (\Illuminate\Http\Request $request) {
//
//    // Ejemplo de IDs (reemplaza con los reales)
//    $brandId     = $request->query('brandId', 8223);
//    $profileID1  = $request->query('profile1', 18031);
//    $profileID2  = $request->query('profile2', 42773);
//    $profileID3  = $request->query('profile3', 54568);
//
//    $profiles = [$profileID1, $profileID2, $profileID3];
//
//    // Query GraphQL
//    $query = <<<'GRAPHQL'
//    query comments($filter: Filter!, $metric: ContentMetric!) {
//        comments(filter: $filter, metric: $metric)
//    }
//    GRAPHQL;
//
//    // Variables que envía la API
//    $variables = [
//        'filter' => [
//            'dateFrom' => $request->query('dateFrom', '2025-01-01T00:00:00Z'),
//            'dateTo'   => $request->query('dateTo', '2025-01-26T23:59:59Z'),
//            'brandId'  => $brandId,
//            'profiles' => $profiles,
//        ],
//        'metric' => $request->query('metric', 'SUM')
//    ];
//
//    // Llamada a Pulsar
//    $response = Http::withHeaders([
//        'Authorization' => 'Bearer ' . env('PULSAR_API_KEY'),
//        'Content-Type'  => 'application/json'
//    ])->post('https://data.pulsarplatform.com/graphql/core', [
//        'query'     => $query,
//        'variables' => $variables
//    ]);
//
//    return response()->json($response->json());
//});