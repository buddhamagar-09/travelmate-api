<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsewaController extends Controller
{
    /**
     * Start eSewa payment
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        // Find the booking
        $booking = Booking::findOrFail($request->booking_id);

        // Make sure the booking belongs to the logged-in user
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Make sure the booking has not already been paid
        if ($booking->payment_status === 'approve') {
            return response()->json([
                'message' => 'This booking has already been paid.',
            ], 400);
        }

        // Get the total amount from the booking
        $totalAmount = $booking->total_price;

        // Generate unique transaction UUID
        $transactionUuid = $booking->id . '-' . Str::uuid();

        // Save transaction UUID
        $booking->update([
            'transaction_uuid' => $transactionUuid,
        ]);

        // eSewa configuration
        $productCode = config('esewaconfig.product_code');
        $secretKey = config('esewaconfig.secret_key');


        // Generate signature
        $signedString =
            "total_amount={$totalAmount}," .
            "transaction_uuid={$transactionUuid}," .
            "product_code={$productCode}";

        $signature = base64_encode(
            hash_hmac(
                'sha256',
                $signedString,
                $secretKey,
                true
            )
        );


        return response()->json([
            'payment_url' => config('esewaconfig.payment_url'),

            'payment_data' => [
                'amount' => $totalAmount,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,

                'transaction_uuid' => $transactionUuid,

                'product_code' => $productCode,

                'product_service_charge' => 0,
                'product_delivery_charge' => 0,

                'success_url' => url('/api/esewa/success'),
                'failure_url' => url('/api/esewa/failure') . '?booking_id=' . $booking->id,
                'signed_field_names' =>
                    'total_amount,transaction_uuid,product_code',

                'signature' => $signature,
            ],
        ]);
    }

    /**
     * eSewa redirects here after successful payment
     */
    public function success(Request $request)
    {
        try {

            // 1. Decode eSewa response
            $data = json_decode(
                base64_decode($request->query('data')),
                true
            );

            if (!$data) {
                return response()->json([
                    'message' => 'Invalid payment response.'
                ], 400);
            }

            // 2. Get payment information
            $transactionUuid = $data['transaction_uuid'] ?? null;
            $signature = $data['signature'] ?? null;
            $signedFields = $data['signed_field_names'] ?? null;

            if (!$transactionUuid || !$signature || !$signedFields) {
                return response()->json([
                    'message' => 'Incomplete payment response.'
                ], 400);
            }

            // 3. Verify signature
            $signedData = collect(explode(',', $signedFields))
                ->map(fn($field) => $field . '=' . ($data[$field] ?? ''))
                ->implode(',');

            $expectedSignature = base64_encode(
                hash_hmac(
                    'sha256',
                    $signedData,
                    config('esewaconfig.secret_key'),
                    true
                )
            );

            if (!hash_equals($expectedSignature, $signature)) {
                return response()->json([
                    'message' => 'Invalid payment signature.'
                ], 400);
            }

            // 4. Find booking
            $booking = Booking::where(
                'transaction_uuid',
                $transactionUuid
            )->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found.'
                ], 404);
            }

            // 5. Check amount
            if ((float) $data['total_amount'] !== (float) $booking->total_price) {
                return response()->json([
                    'message' => 'Payment amount does not match.'
                ], 400);
            }

            // 6. Verify transaction with eSewa
            $response = Http::get(
                config('esewaconfig.status_url'),
                [
                    'product_code' => config('esewaconfig.product_code'),
                    'total_amount' => $booking->total_price,
                    'transaction_uuid' => $transactionUuid,
                ]
            );

            if (
                $response->successful() &&
                $response->json('status') === 'COMPLETE'
            ) {
                $booking->update([
                    'payment_status' => 'paid',
                ]);

                return redirect(
                    'http://localhost:5173/esewa-success/' . $booking->id
                );
            }

            return response()->json([
                'message' => 'Payment was not completed.'
            ], 400);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Payment verification failed.'
            ], 500);
        }
    }
    /**
     * eSewa redirects here if payment fails
     */
    public function failure(Request $request)
    {
        $bookingId = $request->query('booking_id');

        if (!$bookingId) {
            return redirect('http://localhost:5173/my-bookings');
        }

        return redirect(
            'http://localhost:5173/esewa-failure/' . $bookingId
        );
    }
}
