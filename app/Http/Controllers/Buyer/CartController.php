<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cartItems = auth()->user()->cartItems()
            ->with(['listing.images', 'listing.store'])
            ->get();

        $total = $cartItems->sum(fn ($item) => $item->quantity * $item->listing->price);

        return view('buyer.cart.index', compact('cartItems', 'total'));
    }

    public function add(Request $request, Listing $listing): RedirectResponse|JsonResponse
    {
        abort_unless($listing->isAvailable(), 422, 'This item is no longer available.');

        $request->validate([
            'quantity' => ['integer', 'min:1', 'max:' . $listing->stock_quantity],
        ]);

        $qty = $request->integer('quantity', 1);

        $cartItem = CartItem::firstOrNew([
            'user_id'    => auth()->id(),
            'listing_id' => $listing->id,
        ]);

        $newQty = min(($cartItem->quantity ?? 0) + $qty, $listing->stock_quantity);
        $cartItem->quantity = $newQty;
        $cartItem->save();

        if ($request->wantsJson()) {
            $count = auth()->user()->cartItems()->count();
            return response()->json(['success' => true, 'cart_count' => $count]);
        }

        return back()->with('success', '"' . $listing->title . '" added to cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === auth()->id(), 403);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $cartItem->listing->stock_quantity],
        ]);

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === auth()->id(), 403);
        $cartItem->delete();
        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(): RedirectResponse
    {
        auth()->user()->cartItems()->delete();
        return back()->with('success', 'Cart cleared.');
    }
}
