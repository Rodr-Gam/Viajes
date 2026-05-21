<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    // Listar todos los hoteles
    public function index()
    {
        return response()->json(Hotel::with('city')->get(), 200);
    }

    // Registrar un nuevo hotel
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'city_id'         => 'required|exists:cities,id', 
            'address'         => 'required|string|max:255',
            'stars'           => 'required|integer|min:1|max:5',
            'price_per_night' => 'required|numeric|min:0',
            'status'          => 'sometimes|in:active,inactive',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'name_supplier'   => 'nullable|string|max:255',
            'booking_source'  => 'nullable|string|max:255',
            'provider_cost'   => 'nullable|numeric|min:0',
            'observations'    => 'nullable|string',
        ]);

        $hotelData = $request->except('image');

        if ($request->hasFile('image')) {
            $hotelData['image_path'] = $request->file('image')->store('hotels', 'public');
        }

        $hotel = Hotel::create($hotelData);

        return response()->json(['message' => 'Hotel creado!', 'hotel' => $hotel], 201);
    }

    // Ver detalle de un hotel
    public function show($id)
    {
        $hotel = Hotel::with('city')->find($id);
        
        if (!$hotel) return response()->json(['message' => 'No encontrado'], 404);

        return response()->json($hotel, 200);
    }

    // Actualizar hotel
    public function update(Request $request, $id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) return response()->json(['message' => 'No encontrado'], 404);

        $request->validate([
            'name'            => 'sometimes|string|max:255',
            'city_id'         => 'sometimes|exists:cities,id',
            'stars'           => 'sometimes|integer|min:1|max:5',
            'price_per_night' => 'sometimes|numeric',
            'status'          => 'sometimes|in:active,inactive',
            'image'           => 'nullable|image|max:2048',
            'name_supplier'   => 'nullable|string|max:255',
            'booking_source'  => 'nullable|string|max:255',
            'provider_cost'   => 'nullable|numeric|min:0',
            'observations'    => 'nullable|string',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Borrar imagen anterior si existe
            if ($hotel->image_path) {
                Storage::disk('public')->delete($hotel->image_path);
            }
            $data['image_path'] = $request->file('image')->store('hotels', 'public');
        }

        $hotel->update($data);

        return response()->json(['message' => 'Hotel actualizado', 'hotel' => $hotel]);
    }

    // Eliminar hotel (Soft Delete)
    public function destroy($id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) return response()->json(['message' => 'No encontrado'], 404);

        $hotel->delete();
        return response()->json(['message' => 'Hotel eliminado (Soft Delete)'], 200);
    }
}