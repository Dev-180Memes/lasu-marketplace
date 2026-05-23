<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Show checkout page from cart.
     */
    public function checkout(): View
    {
        $cartItems = auth()->user()->cartItems()
            ->with(['listing.store.user', 'listing.images'])
            ->get();

        abort_if($cartItems->isEmpty(), 302, redirect()->route('cart.index')->with('error', 'Your cart is empty.'));

        $total = $cartItems->sum(fn ($i) => $i->quantity * $i->listing->price);

        return view('buyer.orders.checkout', compact('cartItems', 'total'));
    }

    /**
     * Place order(s) — one order per seller.
     */
    public function place(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:online,cash_on_meetup'],
        ]);

        $user      = auth()->user();
        $cartItems = $user->cartItems()->with(['listing.store.user'])->get();

        abort_if($cartItems->isEmpty(), 302, redirect()->route('cart.index'));

        // Validate stock for all items
        foreach ($cartItems as $item) {
            if (!$item->listing->isAvailable() || $item->listing->stock_quantity < $item->quantity) {
                return back()->withErrors("'{$item->listing->title}' is no longer available in the requested quantity.");
            }
        }

        // Group cart items by seller
        $bySeller = $cartItems->groupBy(fn ($i) => $i->listing->user_id);

        $createdOrders = [];

        DB::transaction(function () use ($bySeller, $user, $request, &$createdOrders) {
            foreach ($bySeller as $sellerId => $items) {
                $seller   = $items->first()->listing->store->user;
                $subtotal = $items->sum(fn ($i) => $i->quantity * $i->listing->price);

                $order = Order::create([
                    'order_number'        => Order::generateOrderNumber(),
                    'buyer_id'            => $user->id,
                    'seller_id'           => $sellerId,
                    'payment_method'      => $request->payment_method,
                    'order_status'        => 'pending',
                    'payment_status'      => 'unpaid',
                    'subtotal'            => $subtotal,
                    'total_amount'        => $subtotal,
                    'seller_name_snapshot'=> $seller->name,
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'                => $order->id,
                        'listing_id'              => $item->listing_id,
                        'quantity'                => $item->quantity,
                        'unit_price'              => $item->listing->price,
                        'subtotal'                => $item->quantity * $item->listing->price,
                        'listing_title_snapshot'  => $item->listing->title,
                        'listing_price_snapshot'  => $item->listing->price,
                    ]);

                    // Decrement stock
                    $item->listing->decrement('stock_quantity', $item->quantity);
                    if ($item->listing->stock_quantity <= 0) {
                        $item->listing->update(['availability' => 'out_of_stock']);
                    }
                }

                // Create payment record for online payments
                if ($request->payment_method === 'online') {
                    Payment::create([
                        'order_id'  => $order->id,
                        'user_id'   => $user->id,
                        'provider'  => 'paystack',
                        'amount'    => $order->total_amount,
                        'currency'  => 'NGN',
                        'status'    => 'pending',
                    ]);
                }

                // Notify seller
                $seller->notify(new NewOrderNotification($order));

                $createdOrders[] = $order;
            }

            // Clear cart
            $user->cartItems()->delete();
        });

        $orderIds = collect($createdOrders)->pluck('id')->implode(',');

        if ($request->payment_method === 'online' && count($createdOrders) === 1) {
            return redirect()->route('payment.initiate', $createdOrders[0]->id);
        }

        return redirect()->route('buyer.orders.index')
            ->with('success', 'Order(s) placed successfully! The seller will confirm shortly.');
    }

    /**
     * Buyer's order list.
     */
    public function index(): View
    {
        $orders = Order::where('buyer_id', auth()->id())
            ->with(['items.listing', 'seller', 'payment'])
            ->latest()
            ->paginate(10);

        return view('buyer.orders.index', compact('orders'));
    }

    /**
     * Order detail view.
     */
    public function show(Order $order): View
    {
        abort_unless(
            $order->buyer_id === auth()->id() || $order->seller_id === auth()->id(),
            403
        );

        $order->load(['items.listing.images', 'buyer', 'seller', 'payment', 'meetupProposal.campusZone', 'review']);

        return view('buyer.orders.show', compact('order'));
    }

    /**
     * Buyer confirms they received goods → order complete.
     */
    public function confirmReceived(Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === auth()->id(), 403);
        abort_unless($order->order_status === 'handed_over', 422, 'Order is not in the correct state.');

        $order->update([
            'order_status' => 'completed',
            'completed_at' => now(),
            'payment_status' => $order->payment_method === 'cash_on_meetup' ? 'paid' : $order->payment_status,
            'paid_at'      => $order->payment_method === 'cash_on_meetup' ? now() : $order->paid_at,
        ]);

        return back()->with('success', 'Order marked as completed. You can now leave a review!');
    }

    /**
     * Cancel order (buyer only, while still pending).
     */
    public function cancel(Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === auth()->id(), 403);
        abort_unless($order->isPending(), 422, 'Only pending orders can be cancelled.');

        DB::transaction(function () use ($order) {
            $order->update(['order_status' => 'cancelled', 'cancelled_at' => now()]);

            // Restore stock
            foreach ($order->items as $item) {
                $item->listing?->increment('stock_quantity', $item->quantity);
                if ($item->listing && $item->listing->availability === 'out_of_stock') {
                    $item->listing->update(['availability' => 'available']);
                }
            }
        });

        return back()->with('success', 'Order cancelled.');
    }
}
