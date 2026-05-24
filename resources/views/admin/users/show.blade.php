@extends('layouts.app')
@section('title', 'User: ' . $user->name)
@section('content')
<div class="container py-4" style="max-width:860px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">User Profile</h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row g-4">
        {{-- Profile card --}}
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <img src="{{ $user->avatar_url }}" class="rounded-circle mx-auto mb-3"
                     width="80" height="80" style="object-fit:cover">
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <div class="text-muted small mb-2">{{ $user->email }}</div>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-{{ $user->role === 'admin' ? 'dark' : ($user->role === 'seller' ? 'primary' : 'secondary') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
                <div class="small text-muted">
                    <div><strong>Faculty:</strong> {{ $user->faculty ?? '—' }}</div>
                    <div><strong>Department:</strong> {{ $user->department ?? '—' }}</div>
                    <div><strong>Phone:</strong> {{ $user->phone ?? '—' }}</div>
                    <div><strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}</div>
                    <div><strong>Last login:</strong> {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</div>
                </div>

                @if(!$user->isAdmin())
                    <div class="mt-3 d-flex flex-column gap-2">
                        @if($user->status === 'active')
                            <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-danger btn-sm w-100">Suspend User</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $user->id) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-success btn-sm w-100">Activate User</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Activity --}}
        <div class="col-md-8">
            {{-- Store --}}
            @if($user->store)
                <div class="card p-3 mb-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-shop me-1"></i>Store</h6>
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $user->store->logo_url }}" class="rounded" width="48" height="48" style="object-fit:cover">
                        <div>
                            <div class="fw-semibold">{{ $user->store->name }}</div>
                            <span class="badge bg-{{ match($user->store->status) {
                                'verified'=>'success','suspended'=>'danger',default=>'warning text-dark'
                            } }}">{{ ucfirst($user->store->status) }}</span>
                            <div class="small text-muted mt-1">{{ $user->store->listings->count() }} listings</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Orders stats --}}
            <div class="card p-3 mb-3">
                <h6 class="fw-bold mb-2"><i class="bi bi-bag me-1"></i>Order Activity</h6>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="fw-bold fs-5" style="color:var(--lasu-green)">{{ $user->ordersAsBuyer->count() }}</div>
                        <div class="small text-muted">As Buyer</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5" style="color:var(--lasu-green)">{{ $user->ordersAsSeller->count() }}</div>
                        <div class="small text-muted">As Seller</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 star">{{ number_format($user->average_rating, 1) }}★</div>
                        <div class="small text-muted">Avg Rating</div>
                    </div>
                </div>
            </div>

            {{-- Recent reviews --}}
            @if($user->reviewsReceived->count())
                <div class="card p-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-star me-1"></i>Recent Reviews</h6>
                    @foreach($user->reviewsReceived->take(5) as $review)
                        <div class="border-bottom pb-2 mb-2 small">
                            <div class="star">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                            <div class="text-muted">{{ $review->comment ?? 'No comment' }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ $review->created_at->format('d M Y') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
