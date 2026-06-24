<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\Flight;

// 1. Recursos y Catálogos generales
Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);

// 🚀 NUEVA RUTA PÚBLICA: Para que los clientes vean solo paquetes activos y con stock
Route::get('packages/public', [PackageController::class, 'publicIndex']);
Route::apiResource('packages', PackageController::class);

Route::get('/cities', [CityController::class, 'index']);  
Route::post('/cities', [CityController::class, 'store']); 
Route::apiResource('hotels', HotelController::class);
Route::post('/roles', [RoleController::class, 'store']);

// 2. 🔐 Autenticación de la API
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Reset de la contraseña
Route::post('/forgot-password', function (Request $request) {
    Password::sendResetLink($request->only('email'));
    return response()->json(['message' => 'Link enviado si el correo existe.']);
})->name('password.email');

Route::post('/reset-password', function (Request $request) {
    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Contraseña actualizada.'])
        : response()->json(['message' => 'Token inválido o expirado.'], 400);
})->name('password.reset');


// =========================================================================
// 4. 🔐 RUTAS PROTEGIDAS (AQUÍ ESTÁ LA CORRECCIÓN)
// =========================================================================

// A) 🔑 Para CUALQUIER usuario autenticado (Tanto Clientes como Admins pueden CREAR reservaciones)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reservations', [ReservationController::class, 'store']); // 💡 Movida aquí para evitar el 403
});

// B) 👑 SOLO ADMINISTRADORES (Control total, menos la creación global que ya está arriba)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class);
    
    // Con ->except(['store']) evitamos que choque con la ruta de arriba
    Route::apiResource('reservations', ReservationController::class)->except(['store']); 
    
    Route::apiResource('flights', FlightController::class);
    Route::get('/perfil', function (Request $request) {});
});

// C) 👥 SOLO CLIENTES (Consultar sus propias cosas)
Route::middleware(['auth:sanctum', 'role:cliente'])->group(function () {
    Route::get('/mis-reservas', [ReservationController::class, 'misReservas']);
    Route::get('/mis-reservas/{id}', [ReservationController::class, 'misReservaDetalle']);
});