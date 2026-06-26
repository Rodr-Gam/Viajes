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
use App\Http\Controllers\TransportController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\Flight;

// 1. Recursos y Catálogos generales
Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);

// 🚀 NUEVA RUTA PÚBLICA: Para que los clientes vean solo paquetes activos y con stock
Route::get('packages/public', [PackageController::class, 'publicIndex']);

// Busca dónde tienes las ciudades y déjalas así:
Route::get('/cities', [CityController::class, 'index']);  // 👈 NUEVA: Para listar las ciudades en React
Route::post('/cities', [CityController::class, 'store']); // Esta es la que ya tenías para crear
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


// A) CUALQUIER usuario autenticado 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reservations', [ReservationController::class, 'store']); 
});

// B) ADMINISTRADORES
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('reservations', ReservationController::class)->except(['store']); 
    Route::apiResource('flights', FlightController::class);
    Route::apiResource('packages', PackageController::class);
    Route::apiResource('hotels', HotelController::class);
    Route::apiResource('transports', TransportController::class);


    Route::get('/perfil', function (Request $request) {});
});

// C) CLIENTES 
Route::middleware(['auth:sanctum', 'role:cliente'])->group(function () {
    Route::get('/mis-reservas', [ReservationController::class, 'misReservas']);
    Route::get('/mis-reservas/{id}', [ReservationController::class, 'misReservaDetalle']);
});