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


    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        $query = Reservation::with(['user', 'package.user', 'package.city']);

        if ($request->boolean('without_flight')) {
            $query->whereDoesntHave('flight');
        }

        if ($request->boolean('without_transport')) {
            $query->whereDoesntHave('transport');
        }

        if ($request->boolean('without_hotel')) {
            $query->whereDoesntHave('hotel');
        }

        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }

        if ($request->filled('search')) {
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
            ->with(['package.city', 'flight', 'transport', 'hotel', 'passengers'])
            ->latest()
            ->get();

        return response()->json($reservas);
    }

    public function misReservaDetalle(Request $request, $id)
    {
        $user = $request->user();

        $reserva = Reservation::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['package.city', 'flight', 'passengers'])
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
            'adults' => $reserva->adults,
            'juniors' => $reserva->juniors,
            'children' => $reserva->children,
            'unit_price_adult' => $reserva->unit_price_adult,
            'unit_price_junior' => $reserva->unit_price_junior,
            'unit_price_child' => $reserva->unit_price_child,
            'total_amount' => $reserva->total_amount,
            'state' => $reserva->state,
            'passengers' => $reserva->passengers,
            'package' => $reserva->package,
            'flight' => $reserva->flight ? [
                'airline_name' => $reserva->flight->airline_name,
                'destination' => $reserva->flight->destination,
                'flight_schedule' => $reserva->flight->flight_schedule,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $isAdmin = $request->user()->hasRole('admin');

        $rules = [
            'user_id' => [
                Rule::requiredIf($isAdmin),
                'nullable',
                'exists:users,id',
            ],
            'package_id' => 'required|exists:packages,id',
            'reference_person' => 'nullable|string|max:45',
            'reservation_date' => 'required|date',
            'departure_date' => 'required|date|after_or_equal:reservation_date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'adults' => 'nullable|integer|min:0',
            'juniors' => 'nullable|integer|min:0',
            'children' => 'nullable|integer|min:0',
            'reserved_seats' => 'nullable|integer|min:1',
            'state' => 'required|in:pending,confirmed,canceled,finished,paid',
            'observations' => 'nullable|string|max:500',
        ];
        $data = $request->validate($rules);

        $data['user_id'] = $isAdmin
            ? $data['user_id']
            : $request->user()->id;

        return DB::transaction(function () use ($data) {
            $package = Package::lockForUpdate()->findOrFail($data['package_id']);
            $data = $this->normalizePassengers($data);
            $this->applyPricing($package, $data);
            $seats = $data['reserved_seats'];
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
                'reservation' => $reservation->load(['package.city', 'user']),
            ], 201);
        });
    }

    public function show(Reservation $reservation)
    {
        return response()->json(
            $reservation->load(['package.user', 'package.city', 'user', 'passengers'])
        );
    }

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
            'adults' => 'nullable|integer|min:0',
            'juniors' => 'nullable|integer|min:0',
            'children' => 'nullable|integer|min:0',
            'reserved_seats' => 'nullable|integer|min:1',
            'state' => 'required|in:pending,confirmed,canceled,finished,paid',
            'observations' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($data, $reservation) {
            $isCanceling = $data['state'] === 'canceled';
            $isChangingPackage = (int) $data['package_id'] !== (int) $reservation->package_id;

            $data = $this->normalizePassengers($data, $reservation);
            $oldSeats = $reservation->reserved_seats ?? 0;
            $newSeats = $data['reserved_seats'];

            if ($isChangingPackage) {
                $packageIds = [$reservation->package_id, $data['package_id']];
                sort($packageIds);
                $locked = Package::whereIn('id', $packageIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $oldPackage = $locked[$reservation->package_id];
                $newPackage = $locked[$data['package_id']];
                $this->applyPricing($newPackage, $data);

                if (!$isCanceling && $newPackage->stock < $newSeats) {
                    abort(422, 'No hay suficientes lugares disponibles en el paquete seleccionado.');
                }

                $oldPackage->increment('stock', $oldSeats);

                if (!$isCanceling) {
                    $newPackage->decrement('stock', $newSeats);
                }
            } else {
                $package = Package::lockForUpdate()->findOrFail($data['package_id']);
                $this->applyPricing($package, $data);

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

    /**
     * Normaliza cantidades por tipo de pasajero y calcula reserved_seats total.
     */
    private function normalizePassengers(array $data, ?Reservation $existing = null): array
    {
        $hasBreakdown = array_key_exists('adults', $data)
            || array_key_exists('juniors', $data)
            || array_key_exists('children', $data);

        if ($hasBreakdown) {
            $adults = (int) ($data['adults'] ?? 0);
            $juniors = (int) ($data['juniors'] ?? 0);
            $children = (int) ($data['children'] ?? 0);
        } elseif (array_key_exists('reserved_seats', $data) && $data['reserved_seats'] !== null) {
            $adults = (int) $data['reserved_seats'];
            $juniors = 0;
            $children = 0;
        } elseif ($existing) {
            $adults = (int) ($existing->adults ?? 0);
            $juniors = (int) ($existing->juniors ?? 0);
            $children = (int) ($existing->children ?? 0);
        } else {
            $adults = 0;
            $juniors = 0;
            $children = 0;
        }

        $total = $adults + $juniors + $children;

        if ($total < 1) {
            abort(422, 'Debes reservar al menos un lugar.');
        }

        $data['adults'] = $adults;
        $data['juniors'] = $juniors;
        $data['children'] = $children;
        $data['reserved_seats'] = $total;

        return $data;
    }

    /**
     * Guarda snapshot de precios del paquete y calcula el total de la reserva.
     */
    private function applyPricing(Package $package, array &$data): void
    {
        $data['unit_price_adult'] = $package->price_adult;
        $data['unit_price_junior'] = $package->price_junior;
        $data['unit_price_child'] = $package->price_child;
        $data['total_amount'] =
            ($data['adults'] * $package->price_adult) +
            ($data['juniors'] * $package->price_junior) +
            ($data['children'] * $package->price_child);
    }
}
