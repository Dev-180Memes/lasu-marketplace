@extends('layouts.app')
@section('title', 'Edit Listing')
@section('content')
<div class="container py-4" style="max-width:720px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Edit Listing</h4>
        <a href="{{ route('seller.listings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('seller.listings.update', $listing->id) }}"
              enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $listing->title) }}" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                <textarea name="description" rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          required>{{ old('description', $listing->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Price (₦) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control"
                           value="{{ old('price', $listing->price) }}" min="0" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $listing->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Condition</label>
                    <select name="item_condition" class="form-select" required>
                        <option value="new"         {{ old('item_condition', $listing->item_condition) === 'new'         ? 'selected' : '' }}>New</option>
                        <option value="fairly_used" {{ old('item_condition', $listing->item_condition) === 'fairly_used' ? 'selected' : '' }}>Fairly Used</option>
                        <option value="used"        {{ old('item_condition', $listing->item_condition) === 'used'        ? 'selected' : '' }}>Used</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stock Quantity</label>
                    <input type="number" name="stock_quantity" class="form-control"
                           value="{{ old('stock_quantity', $listing->stock_quantity) }}" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Availability</label>
                    <select name="availability" class="form-select" required>
                        <option value="available"    {{ old('availability', $listing->availability) === 'available'    ? 'selected' : '' }}>Available</option>
                        <option value="out_of_stock" {{ old('availability', $listing->availability) === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Campus Zone</label>
                    <select name="campus_zone_id" class="form-select">
                        <option value="">Any zone</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}"
                                {{ old('campus_zone_id', $listing->campus_zone_id) == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3 d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_negotiable" value="1"
                           id="negotiable" {{ old('is_negotiable', $listing->is_negotiable) ? 'checked' : '' }}>
                    <label class="form-check-label" for="negotiable">Negotiable</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_preorder" value="1"
                           id="preorder" {{ old('is_preorder', $listing->is_preorder) ? 'checked' : '' }}>
                    <label class="form-check-label" for="preorder">Pre-order</label>
                </div>
            </div>

            {{-- Existing images --}}
            @if($listing->images->count())
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Images</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($listing->images as $img)
                            <div class="position-relative">
                                <img src="{{ $img->url }}"
                                     style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:{{ $img->is_primary ? '3px solid var(--lasu-green)' : '1px solid #dee2e6' }}">
                                <div class="form-check position-absolute" style="bottom:2px;left:4px">
                                    <input class="form-check-input" type="checkbox"
                                           name="delete_images[]" value="{{ $img->id }}"
                                           id="del_img_{{ $img->id }}" title="Check to delete">
                                </div>
                                @if($img->is_primary)
                                    <span class="badge bg-success position-absolute" style="top:2px;right:2px;font-size:.55rem">Cover</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">Check an image to delete it.</div>
                </div>
            @endif

            <div class="mb-4">
                <label class="form-label fw-semibold">Add New Photos</label>
                <input type="file" name="new_images[]" class="form-control" multiple accept="image/*">
                <div class="form-text">Upload additional images (max 5 total)</div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-lasu px-4">Save Changes</button>
                <a href="{{ route('seller.listings.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
