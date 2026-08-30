<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function storeReview(Request $request)
    {
        $validatedData = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        //existing review check
        $user_id = $request->user()->id;
        $existingReview = Review::where('package_id', $validatedData['package_id'])
            ->where(
                'user_id',
                $user_id
            )->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already submitted a review for this package.',
            ], 400);
        }

        // Create a new review using the validated data
        $review = Review::create([
            'package_id' => $validatedData['package_id'],
            'user_id' => $request->user()->id,
            'rating' => $validatedData['rating'],
            'comment' => $validatedData['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review,
        ], 201);

    }

    public function index(Request $request)
    {
        $reviews = Review::with(['user:id,name','package:id,title'])->get();

        return response()->json([
            'reviews' => $reviews,
        ]);
    }

    public function togglestatus( Review $review)
    {
        // you can use the id parameter so Review model directly
        // $review = review::findOrFail($id);

        $review->status = $review->status === 'active' ? 'Blocked' :'active';
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'review status changed succesfully',
            'review' => $review,
        ]);
    }

    public function showReviews(Request $request)
    {
        $reviews = Review::with(['user:id,name', 'package:id,title'])
            ->where('status', 'active')
            ->get();

        return response()->json([
            'reviews' => $reviews,
        ]);
    }
}
