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

        foreach($validate['excludes'] as $item){
            $package->excludes()->create([
                'item' => $item,
            ]);
        }

        return response()->json([
            'message' => 'Excludes saved successfully.'
        ], 201);    
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
    public function update(Request $request,package $package)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(package $package)
    {
        //
    }
}
