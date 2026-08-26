<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Package;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = Booking::with(['user', 'package'])
            ->latest()
            ->get();
        
        return response()->json([
            'success' => true,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'phone_number' => 'required|string|max:20',
            'travel_date' => 'required|date|after_or_equal:today',
            'travelers' => 'required|integer|min:1',
            'special_request' => 'nullable|string|max:500',
        ]);

        $package = Package::findOrFail($validated['package_id']);

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'package_id' => $validated['package_id'],
            'phone_number' => $validated['phone_number'],
            'travel_date' => $validated['travel_date'],
            'travelers' => $validated['travelers'],
            'special_request' => $validated['special_request'] ?? null,
            'total_price' => $package->price * $validated['travelers'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
public function updateStatus(Request $request, Booking $booking)
{
    $validated = $request->validate([
        'status' => 'required|in:confirmed,cancelled',
    ]);

    // Don't allow changing a booking once it's finalized
    if ($booking->status !== 'pending') {
        return response()->json([
            'message' => 'This booking has already been processed.'
        ], 400);
    }

    $booking->status = $validated['status'];
    $booking->save();

    return response()->json([
        'success' => true,
        'message' => 'Booking status updated successfully.',
        'booking' => $booking,
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function userBookings(Request $request)
    {
        $bookings = Booking::with(['package'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings,
        ]);

    }

    public function userBookingDetails(Request $request,$id)
    {
        $BookingDetails = Booking::with('user','package')
        ->where('user_id', $request->user()->id)
        ->where('id', $id)
        ->firstorFail();

        return response()->json([
            'success' => true,
            'bookingDetails' => $BookingDetails,
        ]);
    }

    /**
     * Cancel the specified booking.
     */
    public function cancelBooking(Request $request,Booking $booking)
    {

        // Check if the booking belongs to the authenticated user
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to cancel this booking.'
            ], 403);
        }

        // Check if the booking is already cancelled or confirmed
        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'This booking has already been cancelled.'
            ], 400);
        } elseif ($booking->status === 'confirmed') {
            return response()->json([
                'message' => 'This booking has already been confirmed and cannot be cancelled.'
            ], 400);
        }

        // Update the booking status to cancelled
        $booking->status = 'cancelled';
        $booking->save();

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'booking' => $booking,
        ]);
        
    }
}


