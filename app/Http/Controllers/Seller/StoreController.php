<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function show(): View
    {
        $user  = auth()->user();
        $store = $user->store;
        return view('seller.store.show', compact('store'));
    }

    public function create(): View
    {
        return view('seller.store.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->store) {
            return redirect()->route('seller.store.show')->with('info', 'You already have a store.');
        }

        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string', 'max:2000'],
            'logo'               => ['nullable', 'image', 'max:2048'],
            'banner'             => ['nullable', 'image', 'max:4096'],
            'has_fixed_location' => ['boolean'],
            'location_label'     => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'user_id'            => $user->id,
            'name'               => $request->name,
            'slug'               => Str::slug($request->name) . '-' . $user->id,
            'description'        => $request->description,
            'has_fixed_location' => $request->boolean('has_fixed_location'),
            'location_label'     => $request->location_label,
            'status'             => 'unverified',
        ];

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('stores/logos', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('stores/banners', 'public');
        }

        Store::create($data);

        return redirect()->route('seller.dashboard')->with('success', 'Store created! Awaiting admin verification.');
    }

    public function edit(): View
    {
        $store = auth()->user()->store;
        abort_unless($store, 404);
        return view('seller.store.edit', compact('store'));
    }

    public function update(Request $request): RedirectResponse
    {
        $store = auth()->user()->store;
        abort_unless($store, 404);

        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string', 'max:2000'],
            'logo'               => ['nullable', 'image', 'max:2048'],
            'banner'             => ['nullable', 'image', 'max:4096'],
            'has_fixed_location' => ['boolean'],
            'location_label'     => ['nullable', 'string', 'max:255'],
        ]);

        $data = $request->only(['name', 'description', 'has_fixed_location', 'location_label']);
        $data['has_fixed_location'] = $request->boolean('has_fixed_location');

        if ($request->hasFile('logo')) {
            if ($store->logo_path) Storage::disk('public')->delete($store->logo_path);
            $data['logo_path'] = $request->file('logo')->store('stores/logos', 'public');
        }
        if ($request->hasFile('banner')) {
            if ($store->banner_path) Storage::disk('public')->delete($store->banner_path);
            $data['banner_path'] = $request->file('banner')->store('stores/banners', 'public');
        }

        $store->update($data);

        return back()->with('success', 'Store updated successfully.');
    }
}
