@extends('layouts.app')
@section('title', 'Manage Listings')
@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Manage Listings</h4>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="card p-3 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Search title..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <select name="availability" class="form-select form-select-sm">
                    <option value="">All Availability</option>
                    <option value="available"    {{ request('availability') === 'available'    ? 'selected' : '' }}>Available</option>
                    <option value="out_of_stock" {{ request('availability') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="sold"         {{ request('availability') === 'sold'         ? 'selected' : '' }}>Sold</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-lasu btn-sm w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.listings.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Listing</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $listing)
                        <tr class="{{ $listing->trashed() ? 'table-secondary text-muted' : '' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $listing->primary_image_url }}"
                                         style="width:44px;height:44px;object-fit:cover;border-radius:6px"
                                         onerror="this.src='https://via.placeholder.com/44'">
                                    <div class="fw-semibold small" style="max-width:200px">
                                        {{ Str::limit($listing->title, 45) }}
                                    </div>
                                </div>
                            </td>
                            <td class="small">{{ $listing->user->name ?? '—' }}</td>
                            <td class="small">{{ $listing->category->name ?? '—' }}</td>
                            <td class="fw-bold small" style="color:var(--lasu-green)">
                                ₦{{ number_format($listing->price, 2) }}
                            </td>
                            <td class="small">{{ $listing->stock_quantity }}</td>
                            <td>
                                @if($listing->trashed())
                                    <span class="badge bg-secondary">Deleted</span>
                                @else
                                    <span class="badge bg-{{ match($listing->availability) {
                                        'available'    => 'success',
                                        'out_of_stock' => 'warning text-dark',
                                        'sold'         => 'secondary',
                                        default        => 'light text-dark'
                                    } }}">
                                        {{ ucfirst(str_replace('_', ' ', $listing->availability)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $listing->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if(!$listing->trashed())
                                        <a href="{{ route('listings.show', $listing->id) }}"
                                           class="btn btn-xs btn-outline-secondary"
                                           style="font-size:.75rem;padding:2px 8px"
                                           target="_blank">View</a>
                                        <form method="POST" action="{{ route('admin.listings.destroy', $listing->id) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger"
                                                    style="font-size:.75rem;padding:2px 8px"
                                                    onclick="return confirm('Remove this listing?')">
                                                Remove
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.listings.restore', $listing->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-xs btn-outline-success"
                                                    style="font-size:.75rem;padding:2px 8px">
                                                Restore
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No listings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $listings->links() }}</div>
</div>
@endsection