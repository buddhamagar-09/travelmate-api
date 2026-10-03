<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    // GET /api/packages
    public function index()
    {
        return Package::with(['galleries', 'itineraries', 'includes', 'excludes', 'trekHighlights'])->where('status', 'active')
            ->latest()
            ->get();
        // it returns all packages with their related galleries, itineraries, includes, and excludes.
    }

    // GET /api/packages/{id}
    public function show(Package $package)
    {
        // Ensure the package is active
        if ($package->status !== 'active') {
            return response()->json(['message' => 'Package not found'], 404);
        }

        return $package->load(['galleries', 'itineraries', 'includes', 'excludes', 'trekHighlights']);
        // it returns the package with its related galleries, itineraries, includes, and excludes.
    }

    // POST /api/packages
    public function store(Request $request)
    {
        $validated = $request->validate([
            // ---- Shared (all categories) ----
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:packages,slug',
            'category' => 'required|in:trekking,tour,wildlife',
            'location' => 'required|string|max:255',
            'duration' => 'required|string|max:100',
            'price' => 'required|numeric',
            'short_description' => 'required|string',
            'long_description' => 'required|string',
            'featured_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_featured' => 'sometimes|boolean',
            'destination_id' => 'nullable|integer',

            // ---- Optional for all ----
            'group_size' => 'nullable|string|max:100',
            'best_season' => 'nullable|string|max:255',

            // ---- Trekking-only ----
            'difficulty' => 'required_if:category,trekking|nullable|string|max:100',
            'max_altitude' => 'required_if:category,trekking|nullable|string|max:100',

            // ---- Tour-only ----
            'tour_type' => 'required_if:category,tour|nullable|string|max:100',
            'vehicle_type' => 'required_if:category,tour|nullable|string|max:100',

            // ---- Wildlife-only ----
            'park_name' => 'required_if:category,wildlife|nullable|string|max:255',
            // vehicle_type is optional for wildlife (allowed but not required)
        ]);
        // Upload image
        $imagePath = $request->file('featured_image')->store('packages', 'public');

        // Create package
        $package = Package::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'category' => $validated['category'],
            'destination_id' => $validated['destination_id'] ?? null,
            'location' => $validated['location'],
            'duration' => $validated['duration'],
            'price' => $validated['price'],
            'group_size' => $validated['group_size'] ?? null,
            'best_season' => $validated['best_season'] ?? null,
            'short_description' => $validated['short_description'],
            'long_description' => $validated['long_description'],
            'featured_image' => $imagePath,
            'is_featured' => $validated['is_featured'] ?? false,

            // Category-specific (nullable — safe for all)
            'difficulty' => $validated['difficulty'] ?? null,
            'max_altitude' => $validated['max_altitude'] ?? null,
            'tour_type' => $validated['tour_type'] ?? null,
            'park_name' => $validated['park_name'] ?? null,
            'vehicle_type' => $validated['vehicle_type'] ?? null,
        ]);

        return response()->json([
            'message' => 'Package created successfully',
            'package' => $package,
        ], 201);
    }

    // PUT /api/packages/{id}
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            // ---- Shared (all categories) ----
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:packages,slug,' . $package->id,
            'category' => 'sometimes|required|in:trekking,tour,wildlife',
            'destination_id' => 'sometimes|nullable|integer',
            'location' => 'sometimes|required|string|max:255',
            'duration' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric',
            'short_description' => 'sometimes|required|string',
            'long_description' => 'sometimes|required|string',
            'is_featured' => 'sometimes|required|boolean',
            'featured_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',

            // ---- Optional for all ----
            'group_size' => 'sometimes|nullable|string|max:100',
            'best_season' => 'sometimes|nullable|string|max:255',

            // ---- Trekking-only ----
            'difficulty' => 'required_if:category,trekking|nullable|string|max:100',
            'max_altitude' => 'required_if:category,trekking|nullable|string|max:100',

            // ---- Tour-only ----
            'tour_type' => 'required_if:category,tour|nullable|string|max:100',
            'vehicle_type' => 'required_if:category,tour|nullable|string|max:100',

            // ---- Wildlife-only ----
            'park_name' => 'required_if:category,wildlife|nullable|string|max:255',
        ]);

        // Update image if provided
        if ($request->hasFile('featured_image')) {
            if ($package->featured_image) {
                Storage::disk('public')->delete($package->featured_image);
            }

            $validated['featured_image'] = $request->file('featured_image')
                ->store('packages', 'public');
        }

        $package->update($validated);

        return response()->json([
            'message' => 'Package updated successfully',
            'package' => $package->fresh(),
            'package_id' => $package->id,
        ], 200);
    }

    // DELETE /api/packages/{id}
    public function destroy(Package $package)
    {
        // Delete image
        if ($package->featured_image) {
            Storage::disk('public')->delete($package->featured_image);
        }

        $package->delete();

        return response()->json([
            'message' => 'Package deleted successfully',
        ]);
    }

    public function toggleStatus(Package $package)
    {
        $status = $package->status === 'active' ? 'inactive' : 'active';
        $package->status = $status;
        $package->save();

        return response()->json([
            'message' => 'Package status updated successfully',
            'package' => $package,
        ]);
    }

    public function adminIndex()
    {
        return Package::with(['galleries', 'itineraries', 'includes', 'excludes', 'trekHighlights'])
            ->latest()
            ->get();
    }

    public function search(Request $request)
    {
        $query = trim($request->input('query'));

        if (!$query) {
            return response()->json([
                'message' => 'Query parameter is required',
            ], 400);
        }

        $packages = Package::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('slug', 'LIKE', "%{$query}%")
                    ->orWhere('location', 'LIKE', "%{$query}%")
                    ->orWhere('short_description', 'LIKE', "%{$query}%")
                    ->orWhere('long_description', 'LIKE', "%{$query}%");
            })
            ->get();

        return response()->json($packages);
    }

    public function storeTrekHighlight(Request $request, Package $package)
    {
        $validated = $request->validate([
            'highlights' => 'required|array',
            'highlights.*' => 'string|max:255',
        ]);

        foreach ($validated['highlights'] as $item) {
            $package->trekHighlights()->create([
                'highlight' => $item,
            ]);
        }

        return response()->json([
            'message' => 'Trek Highlight saved successfully.',
        ], 201);
    }

    public function showTrekHighlights(Package $package)
    {
        return response()->json([
            'highlights' => $package->trekHighlights()
                ->orderBy('id')
                ->get()
        ]);
    }

    public function updateTrekHighlight(Request $request, Package $package)
    {
        $validated = $request->validate([
            'highlights' => 'required|array|min:1',
            'highlights.*' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($package, $validated) {
            $package->trekHighlights()->delete();

            foreach ($validated['highlights'] as $item) {
                $package->trekHighlights()->create([
                    'highlight' => $item,
                ]);
            }
        });

        return response()->json([
            'message' => 'Trek Highlight updated successfully.',
        ], 200);
    }

    public function deleteTrekHighlight(Package $package)
    {
        $package->trekHighlights()->delete();

        return response()->json([
            'message' => 'Trek Highlight deleted successfully.',
        ], 200);
    }

}
