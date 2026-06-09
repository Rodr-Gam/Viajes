<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index()
    {
        return response()->json(Hotel::with('city')->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'city_id'         => 'required|exists:cities,id', 
            'address'         => 'required|string|max:255',
            'stars'           => 'required|integer|min:1|max:5',
            'price_per_night' => 'required|numeric|min:0',
            'status'          => 'sometimes|in:active,inactive',
            'hgdl_key'        => 'nullable|string|max:20', // ✨ Validando el nuevo campo
            'name_supplier'   => 'nullable|string|max:255',
            'booking_source'  => 'nullable|string|max:255',
            'provider_cost'   => 'nullable|numeric|min:0',
            'observations'    => 'nullable|string',
        ]);

        // Guardamos directamente todo el request ya que no hay que procesar archivos
        $hotel = Hotel::create($request->all());

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
            'hgdl_key'        => 'nullable|string|max:20', // ✨ Validando en la actualización
            'name_supplier'   => 'nullable|string|max:255',
            'booking_source'  => 'nullable|string|max:255',
            'provider_cost'   => 'nullable|numeric|min:0',
            'observations'    => 'nullable|string',
        ]);

        // Actualizamos directo de los datos del request
        $hotel->update($request->all());

        return response()->json(['message' => 'Hotel actualizado', 'hotel' => $hotel]);
    }

    public function destroy($id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) return response()->json(['message' => 'No encontrado'], 404);

        // 🔥 Al no haber imagen en storage, borramos el registro directamente de la base de datos
        $hotel->delete();
        
        return response()->json(['message' => 'Hotel eliminado definitivamente de la base de datos'], 200);
    }
}