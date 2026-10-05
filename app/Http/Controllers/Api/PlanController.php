<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;   // ← change to Plans if your model class is Plans
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    // GET /api/plans — list current user's plans
    public function index(Request $request)
    {
        $plans = Plan::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($plans);
    }

    // GET /api/plans/{plan} — get one plan (must belong to user)
    public function show(Request $request, Plan $plan)
    {
        if ($plan->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Plan not found.',
            ], 404);
        }

        return response()->json($plan);
    }

    // POST /api/plans — create a plan
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|in:trekking,tour,wildlife',
            'location' => 'nullable|string|max:255',
            'budget_min' => 'nullable|integer|min:0',
            'budget_max' => 'nullable|integer|min:0|gte:budget_min',
            'duration' => 'nullable|in:short,medium,long',
            'difficulty' => 'nullable|in:Easy,Moderate,Hard',
            'group_size' => 'nullable|integer|min:1',
        ]);

        $plan = Plan::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'] ?? null,
            'category' => $validated['category'] ?? null,
            'location' => $validated['location'] ?? null,
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'difficulty' => $validated['difficulty'] ?? null,
            'group_size' => $validated['group_size'] ?? null,
        ]);

        return response()->json([
            'message' => 'Plan saved successfully',
            'plan' => $plan,
        ], 201);
    }

    // DELETE /api/plans/{plan}
    public function destroy(Request $request, Plan $plan)
    {
        if ($plan->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Plan not found.',
            ], 404);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully',
        ]);
    }
}
