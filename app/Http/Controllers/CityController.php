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
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $name = trim($validated['name']);
        $country = trim($validated['country'] ?? 'México') ?: 'México';

        $alreadyExists = City::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->whereRaw('LOWER(TRIM(country)) = ?', [mb_strtolower($country)])
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Ya existe un destino con ese nombre y país.',
                'errors' => [
                    'name' => ['Ya existe un destino registrado como "' . $name . '" en ' . $country . '.'],
                ],
            ], 422);
        }

        $city = City::create([
            'name' => $name,
            'country' => $country,
        ]);

        return response()->json([
            'message' => '¡Ciudad agregada con éxito!',
            'city' => $city
        ], 201);
    }
}