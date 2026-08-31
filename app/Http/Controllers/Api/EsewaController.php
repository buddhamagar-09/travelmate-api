<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

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
                'failure_url' => url('/api/esewa/failure'),

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
            // eSewa sends the payment response in the "data" parameter
            $encodedData = $request->query('data');

            if (!$encodedData) {
                return response()->json([
                    'message' => 'Payment response data is missing.'
                ], 400);
            }

            // Decode Base64 response
            $decodedData = base64_decode($encodedData);

            if (!$decodedData) {
                return response()->json([
                    'message' => 'Invalid payment response.'
                ], 400);
            }

            // Convert JSON response to array
            $paymentData = json_decode($decodedData, true);

            if (!$paymentData) {
                return response()->json([
                    'message' => 'Unable to decode payment response.'
                ], 400);
            }

            // Get transaction UUID
            $transactionUuid = $paymentData['transaction_uuid'] ?? null;

            if (!$transactionUuid) {
                return response()->json([
                    'message' => 'Transaction UUID is missing.'
                ], 400);
            }

            // Find the booking
            $booking = Booking::where(
                'transaction_uuid',
                $transactionUuid
            )->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found.'
                ], 404);
            }

            // Verify the amount returned by eSewa
            $totalAmount = $paymentData['total_amount'] ?? null;

            if ((float) $totalAmount !== (float) $booking->total_price) {
                return response()->json([
                    'message' => 'Payment amount does not match booking amount.'
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Verify transaction with eSewa
            |--------------------------------------------------------------------------
            */

            $response = Http::get(
                config('esewaconfig.status_url'),
                [
                    'product_code' => config('esewaconfig.product_code'),
                    'total_amount' => $booking->total_price,
                    'transaction_uuid' => $transactionUuid,
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Unable to verify payment with eSewa.'
                ], 500);
            }

            $verificationData = $response->json();

            /*
            |--------------------------------------------------------------------------
            | Check eSewa payment status
            |--------------------------------------------------------------------------
            */

            if (
                isset($verificationData['status']) &&
                $verificationData['status'] === 'COMPLETE'
            ) {
                $booking->update([
                    'payment_status' => 'approve',
                ]);

                return response()->json([
                    'message' => 'Payment verified successfully.',
                    'booking_id' => $booking->id,
                    'payment_status' => 'approve',
                ]);
            }

            return response()->json([
                'message' => 'Payment was not completed.',
                'status' => $verificationData['status'] ?? 'UNKNOWN',
            ], 400);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Payment verification failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * eSewa redirects here if payment fails
     */
    public function failure(Request $request)
    {
        return response()->json([
            'message' => 'Payment failed',
            'data' => $request->all(),
        ]);
    }
}
