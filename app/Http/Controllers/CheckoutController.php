<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Process payment for a booking.
     * ponytail: placeholder — currently sets payment_status to "paid".
     * Integrate Midtrans here when ready.
     */
    public function __invoke(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(404);
        }

        if ($transaction->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Payment already processed.',
            ], 422);
        }

        $transaction->update(['payment_status' => 'paid']);

        return response()->json([
            'message' => 'Payment successful.',
            'transaction' => $transaction->load([
                'transactionDetails.wasteCategory',
                'paymentFees',
            ]),
        ]);
    }
}
