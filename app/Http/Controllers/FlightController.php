<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $flight = Flight::all();
        return response()->json($flight);
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
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'airline_name' => 'required|string|max:50',
            'destination' => 'required|string|max:50',
            'flight_schedule' => 'required|string|max:255',
            'hgdl_key' => 'required|string|max:20',
            'booking_source' => 'nullable|string|max:50',
            'provider_cost' => 'required|decimal:0,9',
            'observations' => 'nullable|string|max:500',
        ]);

        $flight = Flight::create($data);
        return response()->json([
            'message' => '¡Información de vuelo creada con éxito!',
            'flight' => $flight
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Flight $flight)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Flight $flight)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Flight $flight)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Flight $flight)
    {
        //
    }
}
