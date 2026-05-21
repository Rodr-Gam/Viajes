<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    // 1. Listar todos los paquetes activos con su ciudad
    public function index()
    {
        $packages = Package::with('city')->where('status', 'active')->get();
        return response()->json($packages, 200);
    }

    // 2. Crear un nuevo paquete
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'duration' => 'required|string|max:255',
            'departure_date' => 'required|date|after_or_equal:today',
            'stock' => 'required|integer|min:0',
            'price_adult' => 'required|numeric|min:0',
            'price_junior' => 'required|numeric|min:0',
            'price_child' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $packageData = $request->except('image');

        if ($request->hasFile('image')) {
            $packageData['image_path'] = $request->file('image')->store('packages', 'public');
        }

        $package = Package::create($packageData);

        return response()->json(['message' => '¡Paquete registrado con éxito!', 'package' => $package], 201);
    }

    // 3. Ver detalle de un paquete específico
    public function show($id)
    {
        $package = Package::with('city')->find($id);

        if (!$package) {
            return response()->json(['message' => 'Paquete no encontrado'], 404);
        }

        return response()->json($package, 200);
    }

    // 4. Actualizar un paquete (Usa el truco del _method -> PUT en Postman)
    public function update(Request $request, $id)
    {
        $package = Package::find($id);
        if (!$package) {
            return response()->json(['message' => 'Paquete no encontrado'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'city_id' => 'sometimes|exists:cities,id',
            'duration' => 'sometimes|string|max:255',
            'departure_date' => 'sometimes|date',
            'stock' => 'sometimes|integer|min:0',
            'price_adult' => 'sometimes|numeric|min:0',
            'price_junior' => 'sometimes|numeric|min:0',
            'price_child' => 'sometimes|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($package->image_path) {
                Storage::disk('public')->delete($package->image_path);
            }
            $data['image_path'] = $request->file('image')->store('packages', 'public');
        }

        $package->update($data);

        return response()->json(['message' => 'Paquete actualizado correctamente', 'package' => $package], 200);
    }

    // 5. Eliminar un paquete (Soft Delete)
    public function destroy($id)
    {
        $package = Package::find($id);
        if (!$package) {
            return response()->json(['message' => 'Paquete no encontrado'], 404);
        }

        $package->delete();
        return response()->json(['message' => 'Paquete eliminado'], 200);
    }
}