<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /**
     * Tope de registros por página. Evita que alguien pida
     * ?per_page=999999 y fuerce al servidor a serializar la tabla entera.
     */
    private const MAX_PER_PAGE = 100;
    private const DEFAULT_PER_PAGE = 20;

    /**
     * Display a listing of the resource.
     *
     * Soporta filtros server-side para que el front no tenga que traer
     * toda la tabla y filtrar en memoria: ?search=, ?state=, ?page=, ?per_page=
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        $query = Reservation::with(['user', 'package.user', 'package.city']);

        if ($request->boolean('without_flight')) {
            $query->whereDoesntHave('flight');
        }

        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }

        if ($request->filled('search')) {
            // addcslashes escapa % y _ para que el usuario no pueda inyectar
            // comodines de LIKE y ampliar la búsqueda más allá de lo previsto.
            $search = addcslashes($request->input('search'), '%_');

            $query->where(function ($q) use ($search) {
                $q->where('reference_person', 'like', "%{$search}%")
                    ->orWhereHas('package', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $reservations = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json($reservations);
    }

    public function misReservas(Request $request)
    {
        $user = $request->user();

        $reservas = Reservation::where('user_id', $user->id)
            ->with(['package.city', 'flight'])
            ->latest()
            ->get();

        return response()->json($reservas);
    }

    public function misReservaDetalle(Request $request, $id)
    {
        $user = $request->user();

        $reserva = Reservation::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['package.city', 'flight'])
            ->first();

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        // Solo devolvemos los campos que el cliente puede ver
        return response()->json([
            'id' => $reserva->id,
            'reservation_date' => $reserva->reservation_date,
            'departure_date' => $reserva->departure_date,
            'return_date' => $reserva->return_date,
            'reserved_seats' => $reserva->reserved_seats,
            'state' => $reserva->state,
            'package' => $reserva->package,
            'flight' => $reserva->flight ? [
                'airline_name' => $reserva->flight->airline_name,
                'destination' => $reserva->flight->destination,
                'flight_schedule' => $reserva->flight->flight_schedule,
            ] : null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * El lock + chequeo de stock viven dentro de la misma transacción para
     * que dos reservas concurrentes sobre el mismo paquete no puedan
     * "vender" más cupos de los que existen (overselling).
     */
    public function store(Request $request)
    {
        $rules = [
            'package_id' => 'required|exists:packages,id',
            'reference_person' => 'nullable|string|max:45',
            'reservation_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:reservation_date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'reserved_seats' => [
                'nullable',
                'integer',
                'min:1',
                Rule::requiredIf($request->state === 'confirmed'),
            ],
            'state' => 'required|in:pending,confirmed,canceled,finished,paid',
            'observations' => 'nullable|string|max:500',
        ];
        $data = $request->validate($rules);

        // Solo el admin debe enviar user_id
        if ($request->user()->role === 'admin') {
            $data['user_id'] = $request->user_id;
        } else {
            $data['user_id'] = $request->user()->id;
        }

        // Si es cliente, usar su propio id
        if ($request->user()->role !== 'admin') {
            $data['user_id'] = $request->user()->id;
        }

        return DB::transaction(function () use ($data) {¿
            $package = Package::lockForUpdate()->findOrFail($data['package_id']);
            $seats = $data['reserved_seats'] ?? 0;
            $countsAgainstStock = $data['state'] !== 'canceled';

            if ($countsAgainstStock && $package->stock < $seats) {
                abort(422, 'No hay suficientes lugares en stock.');
            }

            $reservation = Reservation::create($data);

            if ($countsAgainstStock && $seats > 0) {
                $package->decrement('stock', $seats);
            }

            return response()->json([
                'message' => '¡Reserva creada con éxito!',
                'reservation' => $reservation,
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        return response()->json(
            $reservation->load(['package.user', 'package.city', 'user'])
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Maneja tres escenarios dentro de una sola transacción con locks:
     *  1. Cambia el paquete -> devuelve cupos al paquete viejo, resta del nuevo.
     *  2. Mismo paquete, cambia la cantidad de asientos -> ajusta la diferencia.
     *  3. Se cancela -> devuelve los cupos reservados.
     */
    public function update(Request $request, Reservation $reservation)
    {
        if ($reservation->state === 'canceled') {
            return response()->json(['message' => 'Una reserva cancelada no se puede editar'], 403);
        }

        $data = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'reference_person' => 'nullable|string|max:45',
            'reservation_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:reservation_date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'reserved_seats' => [
                'nullable',
                'integer',
                'min:1',
                Rule::requiredIf($request->state === 'confirmed'),
            ],
            'state' => 'required|in:pending,confirmed,canceled,finished,paid',
            'observations' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($data, $reservation) {
            $isCanceling = $data['state'] === 'canceled';
            $isChangingPackage = (int) $data['package_id'] !== (int) $reservation->package_id;

            $oldSeats = $reservation->reserved_seats ?? 0;
            $newSeats = $data['reserved_seats'] ?? 0;

            if ($isChangingPackage) {
                // Bloqueamos ambos paquetes (orden por id evita deadlocks
                // si dos requests cruzan los mismos dos paquetes al revés).
                $packageIds = [$reservation->package_id, $data['package_id']];
                sort($packageIds);
                $locked = Package::whereIn('id', $packageIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $oldPackage = $locked[$reservation->package_id];
                $newPackage = $locked[$data['package_id']];

                if (!$isCanceling && $newPackage->stock < $newSeats) {
                    abort(422, 'No hay suficientes lugares disponibles en el paquete seleccionado.');
                }

                // Devuelve los cupos que tenía reservados en el paquete viejo.
                $oldPackage->increment('stock', $oldSeats);

                if (!$isCanceling) {
                    $newPackage->decrement('stock', $newSeats);
                }
            } else {
                $package = Package::lockForUpdate()->findOrFail($data['package_id']);

                if ($isCanceling) {
                    $package->increment('stock', $oldSeats);
                } else {
                    $diff = $newSeats - $oldSeats;
                    if ($diff > 0 && $package->stock < $diff) {
                        abort(422, 'No hay suficientes lugares disponibles.');
                    }
                    if ($diff !== 0) {
                        $package->decrement('stock', $diff);
                    }
                }
            }

            $reservation->update($data);

            return response()->json([
                'message' => '¡Reserva actualizada con éxito!',
                'reservation' => $reservation->fresh(['package.city', 'package.user']),
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * Soft delete: requiere el trait `SoftDeletes` en el modelo Reservation
     * y una columna `deleted_at` (ver nota al final). Mantener el registro
     * es importante para trazabilidad de reservas pagadas/finalizadas.
     */
    public function destroy(Reservation $reservation)
    {
        return DB::transaction(function () use ($reservation) {
            if (in_array($reservation->state, ['pending', 'confirmed'])) {
                $package = Package::lockForUpdate()->find($reservation->package_id);
                if ($package && $reservation->reserved_seats) {
                    $package->increment('stock', $reservation->reserved_seats);
                }
            }

            $reservation->delete();

            return response()->json(['message' => 'Reserva eliminada correctamente'], 200);
        });
    }
}
