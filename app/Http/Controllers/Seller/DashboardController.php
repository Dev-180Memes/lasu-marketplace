<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user  = auth()->user();
        $store = $user->store;

        $stats = [
            'total_listings'   => $user->listings()->count(),
            'active_listings'  => $user->listings()->available()->count(),
            'pending_orders'   => Order::where('seller_id', $user->id)->where('order_status', 'pending')->count(),
            'completed_orders' => Order::where('seller_id', $user->id)->where('order_status', 'completed')->count(),
            'total_revenue'    => Order::where('seller_id', $user->id)
                ->where('order_status', 'completed')
                ->sum('total_amount'),
            'average_rating'   => $user->average_rating,
        ];

        $recentOrders = Order::where('seller_id', $user->id)
            ->with(['buyer', 'items.listing'])
            ->latest()
            ->take(5)
            ->get();

        return view('seller.dashboard', compact('store', 'stats', 'recentOrders'));
    }
}
