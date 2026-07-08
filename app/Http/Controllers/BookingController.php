<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Http\Requests\BookingRequest;
use App\Models\Address;
use App\Models\CheckoutConfig;
use App\Models\Office;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Create a booking transaction with schedule.
     */
    public function __invoke(BookingRequest $request): JsonResponse
    {
        $user = $request->user();
        $address = Address::where('address_id', $request->validated('address_id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $nearest = $this->findNearestOffice($address->latitude, $address->longitude);

        if ($nearest === null || $nearest->distance > (float) CheckoutConfig::get('max_distance_km', config('checkout.max_distance_km'))) {
            return response()->json([
                'message' => 'No service available in your location.',
            ], 422);
        }

        $distanceFee = (int) round(max($nearest->distance, 1) * (float) CheckoutConfig::get('price_per_km', config('checkout.price_per_km')));
        $totalPaid = max($distanceFee, (int) CheckoutConfig::get('min_fee', config('checkout.min_fee')));

        $transaction = DB::transaction(function () use ($user, $address, $request, $totalPaid, $distanceFee) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'address_id' => $address->address_id,
                'date' => now(),
                'total_paid' => $totalPaid,
                'scheduled_date' => $request->validated('scheduled_date'),
                'time_slot' => $request->validated('time_slot'),
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            foreach ($request->validated('details') as $detail) {
                $td = TransactionDetail::create([
                    'trans_id' => $transaction->trans_id,
                    'category_id' => $detail['category_id'],
                    'actual_weight' => null,
                ]);

                $td->paymentFees()->create([
                    'trans_id' => $transaction->trans_id,
                    'name' => 'Waste Processing Fee',
                    'category' => 'processing',
                    'price' => 0, // ponytail: zero until weight recorded — update when weigh-in happens
                    'currency' => CheckoutConfig::get('currency', config('checkout.currency')),
                ]);
            }

            $transaction->paymentFees()->create([
                'transaction_detail_id' => null,
                'name' => 'Distance Fee',
                'category' => 'distance',
                'price' => $distanceFee,
                'currency' => CheckoutConfig::get('currency', config('checkout.currency')),
            ]);

            return $transaction->load([
                'transactionDetails.wasteCategory',
                'paymentFees',
            ]);
        });

        return response()->json([
            'message' => 'Booking created. Please complete payment.',
            'nearest_office' => [
                'id' => $nearest->id,
                'office_name' => $nearest->office_name,
                'distance_km' => round($nearest->distance, 2),
            ],
            'transaction' => $transaction,
        ], 201);
    }

    /**
     * Confirm payment for a booking.
     */
    public function confirm(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(404);
        }

        if ($transaction->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Payment already confirmed.',
            ], 422);
        }

        $transaction->update([
            'payment_status' => 'confirmed',
            'status' => TransactionStatus::Accepted,
        ]);

        return response()->json([
            'message' => 'Payment confirmed.',
            'transaction' => $transaction->load([
                'transactionDetails.wasteCategory',
                'paymentFees',
            ]),
        ]);
    }

    /**
     * Find nearest office using Haversine formula in PostgreSQL.
     *
     * @return object{id: int, office_name: string, distance: float}|null
     */
    private function findNearestOffice(float $latitude, float $longitude): ?object
    {
        return Office::query()
            ->select('id', 'office_name')
            ->selectRaw('
                (6371 * acos(
                    cos(radians(?)) * cos(radians(address_latitude))
                    * cos(radians(address_longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(address_latitude))
                )) AS distance
            ', [$latitude, $longitude, $latitude])
            ->orderBy('distance')
            ->first();
    }
}
