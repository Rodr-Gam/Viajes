<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validamos usando 'city_id'
        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id', 
            'address' => 'required|string|max:255',
            'stars' => 'required|integer|min:1|max:5',
            'price_per_night' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        // 2. Preparamos los datos (excepto la imagen)
        $hotelData = $request->except('image');

        // 3. Manejo de la imagen
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('hotels', 'public');
            $hotelData['image_path'] = $path;
        }

        // 4. Creamos el hotel
        $hotel = Hotel::create($hotelData);

        return response()->json([
            'message' => '¡Hotel registrado con éxito!',
            'hotel' => $hotel
        ], 201);
    } // Aquí cierra la función store
} // Aquí cierra la clase HotelController