@extends('layouts.app')
@section('title', 'Create Store')
@section('content')
<div class="container py-4" style="max-width:680px">
    <h4 class="fw-bold mb-4">Create Your Store</h4>

    <div class="card p-4">
        <form method="POST" action="{{ route('seller.store.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Store Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="e.g. Amara Books & Stationery" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                <textarea name="description" rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Tell buyers what you sell, where you're located, and what makes your store unique..." required>{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Store Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <div class="form-text">Square image recommended (max 2MB)</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Store Banner</label>
                    <input type="file" name="banner" class="form-control" accept="image/*">
                    <div class="form-text">Wide image for store header (max 4MB)</div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="has_fixed_location"
                           value="1" id="hasLocation" {{ old('has_fixed_location') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="hasLocation">
                        I have a fixed location on campus
                    </label>
                </div>
                <input type="text" name="location_label" class="form-control"
                       value="{{ old('location_label') }}"
                       placeholder="e.g. Faculty of Arts corridor, Room 14">
                <div class="form-text">Where buyers can find you on campus</div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-lasu px-4">Create Store</button>
                <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
