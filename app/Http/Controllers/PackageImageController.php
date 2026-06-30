<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageImage;
use Illuminate\Http\Request;

class PackageImageController extends Controller
{
    /**
     * Subir una imagen al carrusel de un paquete.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'image'      => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_name' => 'nullable|string|max:100',
        ]);

        Package::findOrFail($data['package_id']);

        $path = $request->file('image')->store('package-images', 'public');

        $packageImage = PackageImage::create([
            'package_id' => $data['package_id'],
            'user_id'    => $request->user()->id,
            'image_name' => $data['image_name'] ?? $request->file('image')->getClientOriginalName(),
            'url'        => $path,
        ]);

        return response()->json([
            'message' => 'Imagen agregada al carrusel del paquete.',
            'image'   => $packageImage->fresh(),
        ], 201);
    }

    /**
     * Eliminar una imagen del carrusel.
     */
    public function destroy($id)
    {
        $packageImage = PackageImage::find($id);

        if (!$packageImage) {
            return response()->json(['message' => 'Imagen no encontrada.'], 404);
        }

        $packageImage->deleteStoredFile();
        $packageImage->delete();

        return response()->json(['message' => 'Imagen eliminada correctamente.'], 200);
    }
}
