<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    // GET /api/packages
    public function index()
    {
        return Package::with(['galleries', 'itineraries', 'includes', 'excludes'])
            ->latest()
            ->get();
        // it returns all packages with their related galleries, itineraries, includes, and excludes.
    }

    // GET /api/packages/{id}
    public function show(Package $package)
    {
        return $package->load(['galleries', 'itineraries', 'includes', 'excludes']);
        // it returns the package with its related galleries, itineraries, includes, and excludes.
    }

    // POST /api/packages
    public function store(Request $request)
    {
        // it validates the incoming request data for creating a new package. 
        // The validation rules ensure that all required fields are present and meet specific criteria, such as data type and maximum length. 
        // If the validation fails, Laravel will automatically return a response with the validation errors.
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:packages,slug',
            'location' => 'required|string|max:255',
            'duration' => 'required|string|max:100',
            'price' => 'required|numeric',
            'group_size' => 'required|string|max:100',
            'max_altitude' => 'nullable|string|max:100',
            'difficulty' => 'required|string|max:100',
            'best_season' => 'required|string|max:255',
            'short_description' => 'required|string',
            'long_description' => 'required|string',
            'featured_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_featured' => 'sometimes|boolean',
        ]);

        // Upload image
        $imagePath = $request->file('featured_image')
            ->store('packages', 'public');

        // Create package
        $package = Package::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'location' => $validated['location'],
            'duration' => $validated['duration'],
            'max_altitude' => $validated['max_altitude'] ?? null,
            'price' => $validated['price'],
            'group_size' => $validated['group_size'],
            'difficulty' => $validated['difficulty'],
            'best_season' => $validated['best_season'],
            'short_description' => $validated['short_description'],
            'long_description' => $validated['long_description'],
            'featured_image' => $imagePath,
            'is_featured' => $validated['is_featured'] ?? false,
        ]);

        return response()->json([
            'message' => 'Package created successfully',
            'package' => $package
        ], 201);
    }

    // PUT /api/packages/{id}
    public function update(Request $request, Package $package)
    {
        // Validate the incoming request data for updating an existing package.
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:packages,slug,' . $package->id,
            'location' => 'sometimes|required|string|max:255',
            'duration' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric',
            'group_size' => 'sometimes|required|string|max:100',
            'difficulty' => 'sometimes|required|string|max:100',
            'max_altitude' => 'sometimes|nullable|string|max:100',
            'best_season' => 'sometimes|required|string|max:255',
            'short_description' => 'sometimes|required|string',
            'long_description' => 'sometimes|required|string',
            'is_featured' => 'sometimes|required|boolean',
            'featured_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Update image if provided
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($package->featured_image) {
                Storage::disk('public')->delete($package->featured_image);
            }

            $validated['featured_image'] = $request->file('featured_image')
                ->store('packages', 'public');
        }

        $package->update($validated);

        return response()->json([
            'message' => 'Package updated successfully',
            'package' => $package,
            'package_id' => $package->id
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
            'message' => 'Package deleted successfully'
        ]);
    }

    public function toggleStatus(Package $package)
    {
        $status = $package->status === 'active' ? 'inactive' : 'active';
        $package->status = $status;
        $package->save();

        return response()->json([
            'message' => 'Package status updated successfully',
            'package' => $package
        ]);
    }
}