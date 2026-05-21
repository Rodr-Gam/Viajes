<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\CityController;


use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\FavoriteController;


Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);
Route::apiResource('packages', PackageController::class);
Route::post('/cities', [CityController::class, 'store']);

//Hoteles
Route::apiResource('hotels', HotelController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/roles', [RoleController::class, 'store']);
Route::post('/reservations', [ReservationController::class, 'store'])->middleware('auth:sanctum');
Route::get('/reservations', [ReservationController::class, 'index'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/perfil', function (Request $request) {
    return $request->user();
});