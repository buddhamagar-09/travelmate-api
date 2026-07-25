<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\Package;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    // it returns all itineraries with their related package, ordered by package_id and day_number.
        $itineraries = Itinerary::with('package')
            ->orderBy('package_id')
            ->orderBy('day_number')
            ->get();

        return response()->json($itineraries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Package $package)
    {
        $validated = $request->validate([
            'itinerary' => 'required|array',
            'itinerary.*.day_number' => 'required|integer',
            'itinerary.*.title' => 'required|string|max:255',
            'itinerary.*.description' => 'required|string',
            'itinerary.*.overnight_stay' => 'nullable|string|max:255',
        ]);

        foreach ($validated['itinerary'] as $day) {

            $package->itineraries()->create([
                'day_number' => $day['day_number'],
                'title' => $day['title'],
                'description' => $day['description'],
                'overnight_stay' => $day['overnight_stay'],
                'package_id' => $package->id,
            ]);

        }

        return response()->json([
            'message' => 'Itinerary saved successfully.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {

    // it returns the itineraries for a specific package, ordered by day_number.
        return response()->json([
            'itinerary' => $package->itineraries()
                ->orderBy('day_number')
                ->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'itinerary' => 'required|array',
            'itinerary.*.day_number' => 'required|integer',
            'itinerary.*.title' => 'required|string|max:255',
            'itinerary.*.description' => 'required|string',
            'itinerary.*.overnight_stay' => 'nullable|string|max:255',
        ]);

        // Remove old itinerary
        $package->itineraries()->delete();

        // Insert updated itinerary
        foreach ($validated['itinerary'] as $day) {

            $package->itineraries()->create([
                'day_number' => $day['day_number'],
                'title' => $day['title'],
                'description' => $day['description'],
                'package_id' => $package->id,
                'overnight_stay' => $day['overnight_stay'],
            ]);
        }

        return response()->json([
            'message' => 'Itinerary updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {

    // it deletes all itineraries associated with a specific package.
        $package->itineraries()->delete();

        return response()->json([
            'message' => 'Itinerary deleted successfully.'
        ]);
    }
}
