<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = $request->user()->transactions()
            // limit transaction details to only include waste category to reduce payload size
            ->with(['transactionDetails.wasteCategory'])
            ->latest('trans_id')
            ->cursorPaginate(10, ['status', 'trans_id', 'payment_status', 'total_paid', 'time_slot', 'scheduled_date']);

        return response()->json($transactions);
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(404);
        }

        $transaction->load([
            'transactionDetails.wasteCategory',
            'paymentFees',
            'address',
        ]);

        return response()->json($transaction);
    }
}
