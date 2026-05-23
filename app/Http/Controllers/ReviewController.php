<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $user = auth()->user();

        // Only buyer can review after completion
        abort_unless($order->buyer_id === $user->id, 403);
        abort_unless($order->isCompleted(), 422, 'You can only review completed orders.');
        abort_if($order->review()->where('reviewer_id', $user->id)->exists(), 422, 'You have already reviewed this order.');

        $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::create([
            'order_id'    => $order->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $order->seller_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        return back()->with('success', 'Review submitted. Thank you!');
    }
}
