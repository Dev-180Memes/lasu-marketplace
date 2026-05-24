@extends('layouts.app')
@section('title', $listing->title)
@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('listings.index', ['category' => $listing->category->slug]) }}">{{ $listing->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($listing->title, 40) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Images --}}
        <div class="col-lg-6">
            <div id="listingCarousel" class="carousel slide rounded overflow-hidden shadow-sm" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @forelse($listing->images as $i => $img)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ $img->url }}" class="d-block w-100"
                                 style="height:380px;object-fit:cover" alt="{{ $listing->title }}">
                        </div>
                    @empty
                        <div class="carousel-item active">
                            <img src="{{ $listing->primary_image_url }}" class="d-block w-100"
                                 style="height:380px;object-fit:cover" alt="{{ $listing->title }}">
                        </div>
                    @endforelse
                </div>
                @if($listing->images->count() > 1)
                    <button class="carousel-control-prev" type="button"
                            data-bs-target="#listingCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button"
                            data-bs-target="#listingCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                @endif
            </div>

            {{-- Thumbnails --}}
            @if($listing->images->count() > 1)
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    @foreach($listing->images as $img)
                        <img src="{{ $img->url }}" class="rounded"
                             style="width:64px;height:64px;object-fit:cover;cursor:pointer;border:2px solid #dee2e6">
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div class="col-lg-6">
            <div class="d-flex gap-2 mb-2 flex-wrap">
                <span class="badge" style="background:var(--lasu-light);color:var(--lasu-green)">
                    {{ $listing->category->name }}
                </span>
                <span class="badge {{ $listing->item_condition === 'new' ? 'bg-success' : 'bg-secondary' }}">
                    {{ ucfirst(str_replace('_', ' ', $listing->item_condition)) }}
                </span>
                @if($listing->is_preorder)
                    <span class="badge bg-warning text-dark">Pre-order</span>
                @endif
            </div>

            <h2 class="fw-bold">{{ $listing->title }}</h2>

            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="fs-3 fw-bold" style="color:var(--lasu-green)">
                    {{ $listing->formatted_price }}
                </span>
                @if($listing->is_negotiable)
                    <span class="badge bg-info text-dark">
                        <i class="bi bi-tag me-1"></i>Negotiable
                    </span>
                @endif
            </div>

            <p class="text-muted">{{ $listing->description }}</p>

            {{-- Store card --}}
            <div class="card p-3 mb-3" style="background:var(--lasu-light)">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $listing->store->logo_url }}" class="rounded-circle"
                         width="48" height="48" style="object-fit:cover">
                    <div>
                        <div class="fw-semibold">{{ $listing->store->name }}</div>
                        <div class="small text-muted">
                            @if($listing->store->isVerified())
                                <i class="bi bi-patch-check-fill text-success me-1"></i>Verified Store
                            @else
                                <i class="bi bi-person me-1"></i>{{ $listing->store->user->name }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($listing->campusZone)
                <div class="mb-3 small text-muted">
                    <i class="bi bi-geo-alt me-1"></i>
                    Pickup zone: <strong>{{ $listing->campusZone->name }}</strong>
                </div>
            @endif

            <div class="small text-muted mb-3">
                <i class="bi bi-eye me-1"></i>{{ number_format($listing->view_count) }} views
                &nbsp;
                <i class="bi bi-box me-1"></i>{{ $listing->stock_quantity }} in stock
            </div>

            {{-- Actions --}}
            @auth
                @if(auth()->id() !== $listing->user_id)
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('cart.add', $listing->id) }}">
                            @csrf
                            <button class="btn btn-lasu btn-lg px-4">
                                <i class="bi bi-cart-plus me-1"></i>Add to Cart
                            </button>
                        </form>
                        <form method="POST" action="{{ route('conversations.open', $listing->id) }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-chat-dots me-1"></i>Message Seller
                            </button>
                        </form>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-link btn-sm text-danger p-0"
                                data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="bi bi-flag me-1"></i>Report this listing
                        </button>
                    </div>
                @else
                    <a href="{{ route('seller.listings.edit', $listing->id) }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i>Edit Listing
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-lasu btn-lg px-4">
                    Login to Purchase
                </a>
            @endauth
        </div>
    </div>

    {{-- Related listings --}}
    @if($related->count())
        <div class="mt-5">
            <h5 class="fw-bold mb-3">Related Listings</h5>
            <div class="row g-3">
                @foreach($related as $rel)
                    <div class="col-6 col-md-3">
                        @include('partials.listing-card', ['listing' => $rel])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Report Modal --}}
@auth
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Listing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('reports.store') }}">
                @csrf
                <input type="hidden" name="reportable_type" value="listing">
                <input type="hidden" name="reportable_id" value="{{ $listing->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason</label>
                        <select name="reason" class="form-select" required>
                            <option value="fraud">Fraud / Scam</option>
                            <option value="fake_listing">Fake Listing</option>
                            <option value="spam">Spam</option>
                            <option value="inappropriate_content">Inappropriate Content</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Description <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Provide more details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth
@endsection