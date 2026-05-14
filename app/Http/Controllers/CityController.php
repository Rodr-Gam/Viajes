<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
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