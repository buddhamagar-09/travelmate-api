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
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
