<?php

namespace App\Http\Controllers;

use App\Models\Passenger;
use App\Models\Reservation;
use Illuminate\Http\Request;

class PassengerController extends Controller
{
    public function index(Request $request, Reservation $reservation)
    {
        $this->authorizeReservationAccess($request, $reservation);

        return response()->json(
            $reservation->passengers()->orderBy('type')->orderBy('last_name')->get()
        );
    }

    public function store(Request $request, Reservation $reservation)
    {
        $this->authorizeReservationAccess($request, $reservation);

        if ($reservation->state === 'canceled') {
            return response()->json([
                'message' => 'No se pueden agregar pasajeros a una reserva cancelada.',
            ], 422);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:50',
            'last_name'   => 'required|string|max:50',
            'birth_date'  => 'required|date|before:today',
            'nationality' => 'required|string|max:45',
            'type'        => 'required|in:' . implode(',', Passenger::TYPES),
        ]);

        $this->validateTypeLimit($reservation, $data['type']);

        $passenger = $reservation->passengers()->create($data);

        return response()->json([
            'message'   => 'Pasajero registrado correctamente.',
            'passenger' => $passenger,
        ], 201);
    }

    public function show(Request $request, Reservation $reservation, Passenger $passenger)
    {
        $this->authorizeReservationAccess($request, $reservation);
        $this->ensurePassengerBelongsToReservation($reservation, $passenger);

        return response()->json($passenger);
    }

    public function update(Request $request, Reservation $reservation, Passenger $passenger)
    {
        $this->authorizeReservationAccess($request, $reservation);
        $this->ensurePassengerBelongsToReservation($reservation, $passenger);

        if ($reservation->state === 'canceled') {
            return response()->json([
                'message' => 'No se puede editar pasajeros de una reserva cancelada.',
            ], 422);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:50',
            'last_name'   => 'required|string|max:50',
            'birth_date'  => 'required|date|before:today',
            'nationality' => 'required|string|max:45',
            'type'        => 'required|in:' . implode(',', Passenger::TYPES),
        ]);

        if ($data['type'] !== $passenger->type) {
            $this->validateTypeLimit($reservation, $data['type'], $passenger);
        }

        $passenger->update($data);

        return response()->json([
            'message'   => 'Pasajero actualizado correctamente.',
            'passenger' => $passenger,
        ]);
    }

    public function destroy(Request $request, Reservation $reservation, Passenger $passenger)
    {
        $this->authorizeReservationAccess($request, $reservation);
        $this->ensurePassengerBelongsToReservation($reservation, $passenger);

        if ($reservation->state === 'canceled') {
            return response()->json([
                'message' => 'No se puede eliminar pasajeros de una reserva cancelada.',
            ], 422);
        }

        $passenger->delete();

        return response()->json([
            'message' => 'Pasajero eliminado correctamente.',
        ]);
    }

    private function authorizeReservationAccess(Request $request, Reservation $reservation): void
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('cliente') && (int) $reservation->user_id === (int) $user->id) {
            return;
        }

        abort(403, 'No autorizado para gestionar pasajeros de esta reserva.');
    }

    private function ensurePassengerBelongsToReservation(
        Reservation $reservation,
        Passenger $passenger
    ): void {
        if ((int) $passenger->reservation_id !== (int) $reservation->id) {
            abort(404, 'Pasajero no encontrado en esta reserva.');
        }
    }

    private function validateTypeLimit(
        Reservation $reservation,
        string $type,
        ?Passenger $excluding = null
    ): void {
        $limit = match ($type) {
            'adult'  => (int) ($reservation->adults ?? 0),
            'junior' => (int) ($reservation->juniors ?? 0),
            'child'  => (int) ($reservation->children ?? 0),
        };

        if ($limit < 1) {
            abort(422, "Esta reserva no incluye lugares de tipo {$type}.");
        }

        $query = $reservation->passengers()->where('type', $type);

        if ($excluding) {
            $query->where('id', '!=', $excluding->id);
        }

        if ($query->count() >= $limit) {
            abort(422, "Ya se registraron todos los pasajeros de tipo {$type} para esta reserva.");
        }
    }
}
