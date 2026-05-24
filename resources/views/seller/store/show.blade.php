@extends('layouts.app')
@section('title', 'My Store')
@section('content')
<div class="container py-4" style="max-width:760px">
    @if(!$store)
        <div class="text-center py-5">
            <i class="bi bi-shop fs-1 d-block mb-3 text-muted"></i>
            <h5>You don't have a store yet</h5>
            <p class="text-muted">Create your store to start listing products.</p>
            <a href="{{ route('seller.store.create') }}" class="btn btn-lasu px-4">Create Store</a>
        </div>
    @else
        {{-- Banner --}}
        @if($store->banner_path)
            <img src="{{ $store->banner_url }}" class="w-100 rounded mb-3"
                 style="height:180px;object-fit:cover">
        @endif

        <div class="card p-4 mb-3">
            <div class="d-flex align-items-start gap-4 flex-wrap">
                <img src="{{ $store->logo_url }}" class="rounded" width="80" height="80" style="object-fit:cover">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="fw-bold mb-0">{{ $store->name }}</h4>
                        <span class="badge bg-{{ match($store->status) {
                            'verified'=>'success','suspended'=>'danger',default=>'warning text-dark'
                        } }}">{{ ucfirst($store->status) }}</span>
                    </div>
                    <p class="text-muted mb-2">{{ $store->description }}</p>
                    @if($store->location_label)
                        <div class="small text-muted">
                            <i class="bi bi-geo-alt me-1"></i>{{ $store->location_label }}
                        </div>
                    @endif
                </div>
                <a href="{{ route('seller.store.edit') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit Store
                </a>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Your Listings</h6>
                <a href="{{ route('seller.listings.create') }}" class="btn btn-lasu btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Add Listing
                </a>
            </div>
            @forelse($store->listings()->with('images','category')->latest()->take(6)->get() as $listing)
                <div class="d-flex gap-3 align-items-center border-bottom pb-2 mb-2">
                    <img src="{{ $listing->primary_image_url }}"
                         style="width:48px;height:48px;object-fit:cover;border-radius:6px">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $listing->title }}</div>
                        <div class="small text-muted">{{ $listing->category->name ?? '' }}</div>
                    </div>
                    <div class="fw-bold small" style="color:var(--lasu-green)">{{ $listing->formatted_price }}</div>
                    <a href="{{ route('seller.listings.edit', $listing->id) }}"
                       class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px">Edit</a>
                </div>
            @empty
                <p class="text-muted small">No listings yet. <a href="{{ route('seller.listings.create') }}">Add your first listing</a>.</p>
            @endforelse
        </div>
    @endif
</div>
@endsection
