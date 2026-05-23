<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CampusZone;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function index(): View
    {
        $listings = auth()->user()->listings()
            ->with(['category', 'images'])
            ->withTrashed()
            ->latest()
            ->paginate(15);

        return view('seller.listings.index', compact('listings'));
    }

    public function create(): View
    {
        $store = auth()->user()->store;
        abort_unless($store, 404, 'Please create your store first.');

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $zones      = CampusZone::where('is_active', true)->orderBy('name')->get();

        return view('seller.listings.create', compact('categories', 'zones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user  = auth()->user();
        $store = $user->store;
        abort_unless($store, 404);

        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string', 'max:5000'],
            'price'          => ['required', 'numeric', 'min:0'],
            'category_id'    => ['required', 'exists:categories,id'],
            'campus_zone_id' => ['nullable', 'exists:campus_zones,id'],
            'item_condition' => ['required', 'in:new,fairly_used,used'],
            'stock_quantity' => ['required', 'integer', 'min:1'],
            'is_negotiable'  => ['boolean'],
            'is_preorder'    => ['boolean'],
            'images'         => ['required', 'array', 'min:1', 'max:5'],
            'images.*'       => ['image', 'max:3072'],
        ]);

        DB::transaction(function () use ($validated, $request, $user, $store) {
            $listing = Listing::create([
                ...$validated,
                'user_id'        => $user->id,
                'store_id'       => $store->id,
                'is_negotiable'  => $request->boolean('is_negotiable'),
                'is_preorder'    => $request->boolean('is_preorder'),
                'availability'   => 'available',
                'published_at'   => now(),
            ]);

            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('listings/' . $listing->id, 'public');
                ListingImage::create([
                    'listing_id' => $listing->id,
                    'image_path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()->route('seller.listings.index')->with('success', 'Listing published successfully!');
    }

    public function edit(Listing $listing): View
    {
        $this->authorizeOwnership($listing);

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $zones      = CampusZone::where('is_active', true)->orderBy('name')->get();
        $listing->load('images');

        return view('seller.listings.edit', compact('listing', 'categories', 'zones'));
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeOwnership($listing);

        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string', 'max:5000'],
            'price'          => ['required', 'numeric', 'min:0'],
            'category_id'    => ['required', 'exists:categories,id'],
            'campus_zone_id' => ['nullable', 'exists:campus_zones,id'],
            'item_condition' => ['required', 'in:new,fairly_used,used'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'availability'   => ['required', 'in:available,out_of_stock'],
            'is_negotiable'  => ['boolean'],
            'is_preorder'    => ['boolean'],
            'new_images'     => ['nullable', 'array', 'max:5'],
            'new_images.*'   => ['image', 'max:3072'],
            'delete_images'  => ['nullable', 'array'],
            'delete_images.*'=> ['exists:listing_images,id'],
        ]);

        DB::transaction(function () use ($validated, $request, $listing) {
            $listing->update([
                ...$validated,
                'is_negotiable' => $request->boolean('is_negotiable'),
                'is_preorder'   => $request->boolean('is_preorder'),
            ]);

            // Delete selected images
            if ($request->filled('delete_images')) {
                $toDelete = ListingImage::whereIn('id', $request->delete_images)
                    ->where('listing_id', $listing->id)->get();
                foreach ($toDelete as $img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }

            // Add new images
            if ($request->hasFile('new_images')) {
                $currentMax = $listing->images()->max('sort_order') ?? 0;
                foreach ($request->file('new_images') as $index => $file) {
                    $path = $file->store('listings/' . $listing->id, 'public');
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_path' => $path,
                        'is_primary' => $listing->images()->count() === 0 && $index === 0,
                        'sort_order' => $currentMax + $index + 1,
                    ]);
                }
            }

            // Ensure primary image exists
            if ($listing->fresh()->images()->where('is_primary', true)->doesntExist()) {
                $listing->images()->first()?->update(['is_primary' => true]);
            }
        });

        return back()->with('success', 'Listing updated successfully.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        $this->authorizeOwnership($listing);
        $listing->delete();
        return back()->with('success', 'Listing removed.');
    }

    private function authorizeOwnership(Listing $listing): void
    {
        abort_unless($listing->user_id === auth()->id(), 403);
    }
}
