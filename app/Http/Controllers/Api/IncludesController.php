<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Include;

class IncludesController extends Controller
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
       'includes' => 'required|array',
       'includes.*' => 'required|string|max:255',
        ]);

        foreach($validate['includes'] as $item){
            $package->includes()->create([
                'item' => $item,
            ]);
        }

        return response()->json([
            'message' => 'Includes saved successfully.'
        ], 201);    
    }

    /**
     * Display the specified resource.
     */
    public function show(package $package)
    {
        return response()->json([
            'includes' => $package->includes()
            ->orderBy('id')
            ->get()
        ]);  
    }
   

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, package $package)
    {
        //
        $validated = $request->validate([
            'includes' => 'required|array',
            'includes.*' => 'required|string|max:255'
        ]);

        //delete existing includes for the package
        $package->includes()->delete();

        //create new includes for the package
        foreach($validated['includes'] as $newIncludes){
            $package->includes()->create([
                'item' => $newIncludes
            ]);
        }

        return response()->json([
            'message' => 'Includes updated successfully.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(package $package)
    {
        //delete all includes for the package
        $package->includes()->delete();

        return response()->json([
            'message' => 'Includes deleted successfully.'
        ], 200);
    }
}
