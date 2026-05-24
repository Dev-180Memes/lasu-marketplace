@extends('layouts.app')
@section('title', 'Edit Store')
@section('content')
<div class="container py-4" style="max-width:680px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Edit Store</h4>
        <a href="{{ route('seller.store.show') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('seller.store.update') }}" enctype="multipart/form-data">
            @csrf @method('PATCH')

            <div class="mb-3">
                <label class="form-label fw-semibold">Store Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $store->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                <textarea name="description" rows="4" class="form-control" required>{{ old('description', $store->description) }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Store Logo</label>
                    @if($store->logo_path)
                        <div class="mb-2">
                            <img src="{{ $store->logo_url }}" class="rounded" width="56" height="56" style="object-fit:cover">
                            <span class="small text-muted ms-2">Current logo</span>
                        </div>
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Store Banner</label>
                    @if($store->banner_path)
                        <div class="mb-2">
                            <img src="{{ $store->banner_url }}" class="rounded w-100"
                                 style="height:56px;object-fit:cover">
                        </div>
                    @endif
                    <input type="file" name="banner" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="has_fixed_location"
                           value="1" id="hasLocation"
                           {{ old('has_fixed_location', $store->has_fixed_location) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="hasLocation">
                        I have a fixed location on campus
                    </label>
                </div>
                <input type="text" name="location_label" class="form-control"
                       value="{{ old('location_label', $store->location_label) }}"
                       placeholder="e.g. Faculty of Arts corridor, Room 14">
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-lasu px-4">Save Changes</button>
                <a href="{{ route('seller.store.show') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
