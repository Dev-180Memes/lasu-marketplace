<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $featured = Listing::with(['images', 'store', 'category'])
            ->published()
            ->available()
            ->latest('published_at')
            ->take(8)
            ->get();

        return view('home', compact('categories', 'featured'));
    }

    public function listings(Request $request): View
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $query = Listing::with(['images', 'store', 'category', 'campusZone'])
            ->published()
            ->available();

        // Search
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Condition filter
        if ($request->filled('condition')) {
            $query->where('item_condition', $request->condition);
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular'    => $query->orderBy('view_count', 'desc'),
            default      => $query->latest('published_at'),
        };

        $listings = $query->paginate(12)->withQueryString();

        return view('listings.index', compact('listings', 'categories'));
    }

    public function show(Listing $listing): View
    {
        // Increment view count
        $listing->increment('view_count');

        $listing->load(['images', 'store.user', 'category', 'campusZone']);

        $related = Listing::with(['images', 'store'])
            ->published()->available()
            ->where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->take(4)
            ->get();

        return view('listings.show', compact('listing', 'related'));
    }
}
