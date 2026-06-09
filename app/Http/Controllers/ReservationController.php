<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::with(['package.user', 'package.city'])->get();
        return response()->json($reservations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('admin')) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $data = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'reference_person' => 'nullable|string|max:45',
            'reservation_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:reservation_date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'reserved_seats' => 'nullable|integer',
            'state' => 'required|in:pending,confirmed,canceled,finished,paid',
            'observations' => 'nullable|string|max:500',
        ]);

        $package = \App\Models\Package::findOrFail($data['package_id']);

        if ($package->stock < $data['reserved_seats']) {
            return response()->json([
                'message' => 'No hay suficientes lugares en stock.'
            ], 422);
        }

        $reservation = Reservation::create($data);

        $package->decrement('stock', $data['reserved_seats']);
        return response()->json([
            'message' => '¡Reserva creada con éxito!',
            'reservation' => $reservation
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        return response()->json(
            $reservation->load(['package.user'])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {

        $data = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'reference_person' => 'nullable|string|max:45',
            'reservation_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:reservation_date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'reserved_seats' => 'nullable|integer',
            'state' => 'required|in:pending,confirmed,canceled,finished,paid',
            'observations' => 'nullable|string|max:500',
        ]);

        $package = $reservation->package;
        $asientosReservados = $reservation->reserved_seats;
        $asientosNuevos = $data['reserved_seats'];
        $reservaCancelada = $reservation->state;

        $isCanceling = $data['state'] === 'canceled' && $reservation->getOriginal('state') !== 'canceled';

        if ($reservaCancelada === 'canceled') {
            return response()->json(['message' => 'Una reserva cancelada no se puede editar'], 403);
        }
        if ($asientosReservados !== $asientosNuevos && !$isCanceling) {
            $diferencia = $asientosNuevos - $asientosReservados;

            if ($diferencia > 0 && $package->stock < $diferencia) {
                return response()->json(['message' => 'No hay suficientes lugares disponibles.'], 422);
            }

            $package->decrement('stock', $diferencia);
        }

        $reservation->update($data);

        if ($isCanceling) {
            $package->increment('stock', $asientosReservados);
            return response()->json([
                'message' => '¡Reserva actualizada con éxito!',
                'reservation' => $reservation
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        //
    }
}
