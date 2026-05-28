<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // 👈 Maneja la seguridad (Login/Register)
use App\Http\Controllers\UserController;     // 👈 NUEVO: Maneja el CRUD administrativo
use App\Http\Controllers\RoleController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\FavoriteController;

// 1. Recursos y Catálogos generales
Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);
Route::apiResource('packages', PackageController::class);
// Busca dónde tienes las ciudades y déjalas así:
Route::get('/cities', [CityController::class, 'index']);  // 👈 NUEVA: Para listar las ciudades en React
Route::post('/cities', [CityController::class, 'store']); // Esta es la que ya tenías para crear
Route::apiResource('hotels', HotelController::class);
Route::post('/roles', [RoleController::class, 'store']);

// 2. 🔐 Autenticación de la API (Apuntando a tu AuthController)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 3. 🛠️ CRUD Administrativo de Usuarios (Apuntando a tu UserController completo)
Route::apiResource('users', UserController::class);

// 4. 🛡️ Rutas Protegidas (Solo entran los que tengan un Token válido)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations', [ReservationController::class, 'index']);
    
    Route::get('/perfil', function (Request $request) {
        return $request->user();
    });
});