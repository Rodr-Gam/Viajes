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
        $reservations = Reservation::with('package')->get();
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
            'state' => 'required|in:pending,confirmed,canceled,finished',
            'observations' => 'nullable|string|max:500',
        ]);

        $reservation = Reservation::create($data);

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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        //
    }
}
