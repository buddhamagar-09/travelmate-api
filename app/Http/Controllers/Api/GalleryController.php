<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
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
    public function add(Request $request, string $package)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $package = Package::findOrFail($package);

        foreach ($request->file('images') as $image) {

            $path = $image->store('gallery', 'public');

            $package->galleries()->create([
                'image_path' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Gallery uploaded successfully.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        return response()->json([
            'gallery' => $package->galleries
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        if ($request->hasFile('images')) {

            // Delete existing images
            foreach ($package->galleries as $gallery) {
                Storage::disk('public')->delete($gallery->image_path);
                $gallery->delete();
            }

            // Store new images
            foreach ($request->file('images') as $image) {
                $path = $image->store('gallery', 'public');
                $package->galleries()->create([
                    'image_path' => $path,
                ]);
            }
        }



        return response()->json([
            'message' => 'Gallery updated successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        //
    }
}
