<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\PartialPayment;
use Illuminate\Http\Request;

class PartialPaymentController extends Controller
{
    public function index(Account $account)
    {
        return response()->json(
            $account->partialPayments()->latest('paid_at')->get()
        );
    }

    public function store(Request $request, Account $account)
    {
        if ($account->state === 'canceled') {
            return response()->json([
                'message' => 'No se pueden registrar pagos en una cuenta cancelada.',
            ], 422);
        }

        $data = $request->validate([
            'amount'           => 'required|numeric|min:0.0001|max:9999999999999.9999',
            'transaction_type' => 'required|in:' . implode(',', PartialPayment::TRANSACTION_TYPES),
            'paid_at'          => 'required|date',
            'payment_method'   => 'required|in:' . implode(',', PartialPayment::PAYMENT_METHODS),
            'sent_to_supplier' => 'nullable|string|max:255',
            'confirmation'     => 'nullable|string|max:45',
        ]);

        if ($data['transaction_type'] === 'payment' && $data['amount'] > $account->balanceDue()) {
            return response()->json([
                'message' => 'El pago supera el saldo pendiente de la cuenta.',
            ], 422);
        }

        $payment = $account->partialPayments()->create($data);
        $account->recalculateState();

        return response()->json([
            'message' => 'Pago registrado correctamente.',
            'payment' => $payment,
            'account' => $account->fresh(),
        ], 201);
    }

    public function show(Account $account, PartialPayment $partialPayment)
    {
        $this->ensureBelongsToAccount($account, $partialPayment);

        return response()->json($partialPayment);
    }

    public function update(Request $request, Account $account, PartialPayment $partialPayment)
    {
        $this->ensureBelongsToAccount($account, $partialPayment);

        if ($account->state === 'canceled') {
            return response()->json([
                'message' => 'No se pueden editar pagos de una cuenta cancelada.',
            ], 422);
        }

        $data = $request->validate([
            'amount'           => 'required|numeric|min:0.0001|max:9999999999999.9999',
            'transaction_type' => 'required|in:' . implode(',', PartialPayment::TRANSACTION_TYPES),
            'paid_at'          => 'required|date',
            'payment_method'   => 'required|in:' . implode(',', PartialPayment::PAYMENT_METHODS),
            'sent_to_supplier' => 'nullable|string|max:255',
            'confirmation'     => 'nullable|string|max:45',
        ]);

        $partialPayment->update($data);
        $account->recalculateState();

        return response()->json([
            'message' => 'Pago actualizado correctamente.',
            'payment' => $partialPayment,
            'account' => $account->fresh(),
        ]);
    }

    public function destroy(Account $account, PartialPayment $partialPayment)
    {
        $this->ensureBelongsToAccount($account, $partialPayment);

        if ($account->state === 'canceled') {
            return response()->json([
                'message' => 'No se pueden eliminar pagos de una cuenta cancelada.',
            ], 422);
        }

        $partialPayment->delete();
        $account->recalculateState();

        return response()->json(['message' => 'Pago eliminado correctamente.']);
    }

    private function ensureBelongsToAccount(Account $account, PartialPayment $partialPayment): void
    {
        if ((int) $partialPayment->account_id !== (int) $account->id) {
            abort(404, 'Pago no encontrado en esta cuenta.');
        }
    }
}
