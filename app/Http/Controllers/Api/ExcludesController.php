<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Exclude;

class ExcludesController extends Controller
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
    public function store(Request $request, Package $package)
    {
          $validate = $request->validate([
       'excludes' => 'required|array',
       'excludes.*' => 'required|string|max:255',
        ]);

        // Delete existing excludes
        $package->excludes()->delete();

        foreach($validate['excludes'] as $NewExclude){
            $package->excludes()->create([
                'item' => $NewExclude,
            ]);
        }

        return response()->json([
            'message' => 'Excludes saved successfully.'
        ], 201);    
    }

    /**
     * Display the specified resource.
     */
    public function show(package $package)
    {
        return response()->json([
            'excludes' => $package->excludes()
            ->orderBy('id')
            ->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,package $package)
    {
        $validate = $request->validate([
       'excludes' => 'required|array',
       'excludes.*' => 'required|string|max:255',
        ]);

        // Delete existing excludes
        $package->excludes()->delete();

        foreach($validate['excludes'] as $NewExclude){
            $package->excludes()->create([
                'item' => $NewExclude,
            ]);
        }

        return response()->json([
            'message' => 'Excludes updated successfully.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(package $package)
    {
        $package->excludes()->delete();
        return response()->json([
            'message' => 'Excludes deleted successfully.'
        ], 200);
    }
}
