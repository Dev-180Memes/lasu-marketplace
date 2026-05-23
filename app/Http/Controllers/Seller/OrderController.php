<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::where('seller_id', auth()->id())
            ->with(['buyer', 'items.listing', 'payment'])
            ->latest()
            ->paginate(10);

        return view('seller.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->seller_id === auth()->id(), 403);
        $order->load(['items.listing.images', 'buyer', 'payment', 'meetupProposal.campusZone']);
        return view('seller.orders.show', compact('order'));
    }

    public function confirm(Order $order): RedirectResponse
    {
        abort_unless($order->seller_id === auth()->id(), 403);
        abort_unless($order->isPending(), 422, 'Order is not pending.');

        $order->update(['order_status' => 'confirmed', 'confirmed_at' => now()]);

        return back()->with('success', 'Order confirmed! Arrange meetup with buyer.');
    }

    public function markHandedOver(Order $order): RedirectResponse
    {
        abort_unless($order->seller_id === auth()->id(), 403);
        abort_unless($order->isConfirmed(), 422, 'Order must be confirmed before handover.');

        $order->update(['order_status' => 'handed_over', 'handed_over_at' => now()]);

        return back()->with('success', 'Marked as handed over. Waiting for buyer confirmation.');
    }
}
