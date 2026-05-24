@extends('layouts.app')
@section('title', 'Edit Campus Zone')
@section('content')
<div class="container py-4" style="max-width:600px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Edit Campus Zone</h4>
        <a href="{{ route('admin.zones.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('admin.zones.update', $zone->id) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Zone Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $zone->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $zone->description) }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Latitude</label>
                    <input type="number" name="latitude" step="0.00000001"
                           class="form-control" value="{{ old('latitude', $zone->latitude) }}">
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Longitude</label>
                    <input type="number" name="longitude" step="0.00000001"
                           class="form-control" value="{{ old('longitude', $zone->longitude) }}">
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                           id="is_active" {{ old('is_active', $zone->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-lasu px-4">Update Zone</button>
                <a href="{{ route('admin.zones.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
