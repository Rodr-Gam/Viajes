<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'packages'])->get();
        return response()->json($users, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:45',
            'last_name' => 'required|string|max:45',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'state' => 'sometimes|in:active,inactive,banned',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'state' => $request->state ?? 'active', 
            'role_id' => $request->role_id,
        ]);

        return response()->json([
            'message' => '¡Usuario creado con éxito por el administrador!',
            'user' => $user
        ], 201);
    }

    public function show($id)
    {
        $user = User::with(['role', 'packages'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:45',
            'last_name' => 'sometimes|string|max:45',
            'email' => 'sometimes|string|email|max:100|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'sometimes|exists:roles,id',
            'state' => 'sometimes|in:active,inactive,banned',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 400);
        }

        $user->name = $request->name ?? $user->name;
        $user->last_name = $request->last_name ?? $user->last_name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->role_id = $request->role_id ?? $user->role_id;
        $user->state = $request->state ?? $user->state;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => '¡Usuario actualizado con éxito!',
            'user' => $user
        ], 200);
    }

    // 🧨 ¡BORRADO FÍSICO REAL DIRECTO!
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $user->delete(); 

        return response()->json([
            'message' => '¡Usuario eliminado definitivamente de la base de datos!'
        ], 200);
    }
}