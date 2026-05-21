<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // 1. Listar los favoritos (De todos, o puedes filtrar por user_id)
    public function index(Request $request)
    {
        // Si mandan ?user_id=1 en la URL, filtramos por usuario
        $query = Favorite::with(['user', 'hotel', 'package']);
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->get(), 200);
    }

    // 2. Agregar un favorito
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'nullable|exists:packages,id',
            'hotel_id' => 'nullable|exists:hotels,id',
        ]);

        // Validación personalizada: Debe haber exactamente UNO de los dos
        if (!$request->package_id && !$request->hotel_id) {
            return response()->json(['message' => 'Debes enviar un package_id o un hotel_id.'], 400);
        }

        if ($request->package_id && $request->hotel_id) {
            return response()->json(['message' => 'Solo puedes marcar como favorito un paquete O un hotel a la vez, no ambos.'], 400);
        }

        // Evitar duplicados exactos
        $exists = Favorite::where('user_id', $request->user_id)
            ->where('package_id', $request->package_id)
            ->where('hotel_id', $request->hotel_id)
            ->first();

        if ($exists) {
            return response()->json(['message' => 'Este elemento ya está en tus favoritos.'], 409); // 409 Conflict
        }

        $favorite = Favorite::create($request->all());

        return response()->json(['message' => '¡Agregado a favoritos!', 'favorite' => $favorite], 201);
    }

    // 3. Eliminar un favorito
    public function destroy($id)
    {
        $favorite = Favorite::find($id);
        
        if (!$favorite) {
            return response()->json(['message' => 'Favorito no encontrado'], 404);
        }

        $favorite->delete();
        return response()->json(['message' => 'Eliminado de favoritos'], 200);
    }
}