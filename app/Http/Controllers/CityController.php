<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    // 🚀 NUEVO: Regresa la lista de todas las ciudades a React
    public function index()
    {
        $cities = City::all(); 
        return response()->json($cities, 200);
    }

    // Tu función de guardar que ya tenías perfecta
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $city = City::create($request->all());

        return response()->json([
            'message' => '¡Ciudad agregada con éxito!',
            'city' => $city
        ], 201);
    }
}