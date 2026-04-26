<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tour;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $tourId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user has completed booking for this tour
        $hasBooking = Booking::where('user_id', auth()->id())
                            ->where('tour_id', $tourId)
                            ->where('status', 'completed')
                            ->exists();

        if (!$hasBooking) {
            return response()->json(['message' => 'Bạn chỉ có thể đánh giá tour đã hoàn thành.'], 403);
        }

        Review::updateOrCreate(
            ['user_id' => auth()->id(), 'tour_id' => $tourId],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        return response()->json(['message' => 'Đánh giá đã được lưu.']);
    }

    public function index($tourId)
    {
        $reviews = Review::with('user')->where('tour_id', $tourId)->get();
        return response()->json($reviews);
    }

    // Web method
    public function storeWeb(Request $request, $tourId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $hasBooking = Booking::where('user_id', auth()->id())
                            ->where('tour_id', $tourId)
                            ->where('status', 'completed')
                            ->exists();

        if (!$hasBooking) {
            return back()->withErrors(['message' => 'Bạn chỉ có thể đánh giá tour đã hoàn thành.']);
        }

        Review::updateOrCreate(
            ['user_id' => auth()->id(), 'tour_id' => $tourId],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        return back()->with('success', 'Đánh giá đã được lưu.');
    }
}
