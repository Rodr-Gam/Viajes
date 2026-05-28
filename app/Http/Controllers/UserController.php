<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * 1. INDEX: Listar todos los usuarios
     * Ideal para meterlos en una tabla en el frontend.
     */
    public function index()
    {
        // Traemos los usuarios junto con su rol usando la relación del modelo
        $users = User::with('role')->get();
        return response()->json($users, 200);
    }

    /**
     * 2. STORE: Crear un usuario manualmente (Desde el Admin)
     * Conservamos tu lógica original pero permitiendo asignar el estado.
     */
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
            'state' => $request->state ?? 'active', // Si no envían estado, cae en active
            'role_id' => $request->role_id,
        ]);

        return response()->json([
            'message' => '¡Usuario creado con éxito por el administrador!',
            'user' => $user
        ], 201);
    }

    /**
     * 3. SHOW: Ver los detalles de un solo usuario
     * Útil cuando le das clic a "Ver Perfil" o "Editar" en una fila.
     */
    public function show($id)
    {
        $user = User::with('role')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        return response()->json($user, 200);
    }

    /**
     * 4. UPDATE: Modificar los datos de un usuario existente
     * Ojo al truco del correo y la contraseña.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:45',
            'last_name' => 'sometimes|string|max:45',
            // El truco 'unique:users,email,'.$id le dice a Laravel: "El email debe ser único, pero ignora el de este usuario"
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

        // Actualizamos los campos estándar si vienen en la petición
        $user->name = $request->name ?? $user->name;
        $user->last_name = $request->last_name ?? $user->last_name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->role_id = $request->role_id ?? $user->role_id;
        $user->state = $request->state ?? $user->state;

        // Si el admin escribió algo en la contraseña, la encriptamos. Si la dejó vacía, no la tocamos.
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => '¡Usuario actualizado con éxito!',
            'user' => $user
        ], 200);
    }

    /**
     * 5. DESTROY: Borrar un usuario del mapa
     * Como tu modelo usa SoftDeletes, esto no lo borra físicamente,
     * solo le pone fecha en 'deleted_at' (se va a la papelera).
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $user->delete(); // Aplica el Soft Delete automáticamente

        return response()->json([
            'message' => '¡Usuario eliminado correctamente de la lista activa!'
        ], 200);
    }
}