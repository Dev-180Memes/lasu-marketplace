@extends('layouts.app')
@section('title', 'Order #' . $order->order_number)
@section('content')
<div class="container py-4" style="max-width:780px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Order #{{ $order->order_number }}</h4>
        <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card p-4 mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <div class="text-muted small">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</div>
                <div class="small mt-1">Payment: <strong>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</strong></div>
            </div>
            <span class="badge fs-6 bg-{{ match($order->order_status) {
                'completed'=>'success','cancelled'=>'danger',
                'confirmed','handed_over'=>'primary',default=>'warning text-dark'
            } }}">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
        </div>
    </div>

    {{-- Items --}}
    <div class="card p-4 mb-3">
        <h6 class="fw-bold mb-3">Items</h6>
        @foreach($order->items as $item)
            <div class="d-flex gap-3 align-items-center mb-3">
                <img src="{{ $item->listing?->primary_image_url ?? asset('images/placeholder.png') }}"
                     style="width:64px;height:64px;object-fit:cover;border-radius:8px">
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $item->listing_title_snapshot }}</div>
                    <div class="small text-muted">Qty: {{ $item->quantity }} × ₦{{ number_format($item->unit_price, 2) }}</div>
                </div>
                <div class="fw-bold" style="color:var(--lasu-green)">₦{{ number_format($item->subtotal, 2) }}</div>
            </div>
        @endforeach
        <div class="border-top pt-3 d-flex justify-content-between fw-bold">
            <span>Total</span>
            <span style="color:var(--lasu-green)">{{ $order->formatted_total }}</span>
        </div>
    </div>

    {{-- Buyer info --}}
    <div class="card p-3 mb-3">
        <h6 class="fw-bold mb-2">Buyer</h6>
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $order->buyer->avatar_url }}" class="rounded-circle" width="40" height="40" style="object-fit:cover">
            <div>
                <div class="fw-semibold">{{ $order->buyer->name }}</div>
                <div class="small text-muted">{{ $order->buyer->email }}</div>
                <div class="small text-muted">{{ $order->buyer->phone ?? 'No phone' }}</div>
            </div>
        </div>
    </div>

    {{-- Meetup --}}
    @if($order->meetupProposal)
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-geo-alt me-1"></i>Agreed Meetup</h6>
            <div><strong>Zone:</strong> {{ $order->meetupProposal->campusZone->name }}</div>
            <div><strong>Time:</strong> {{ $order->meetupProposal->proposed_at->format('d M Y, h:i A') }}</div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="card p-3">
        <h6 class="fw-bold mb-2">Actions</h6>
        <div class="d-flex gap-2 flex-wrap">
            @if($order->isPending())
                <form method="POST" action="{{ route('seller.orders.confirm', $order->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Confirm Order</button>
                </form>
            @elseif($order->isConfirmed())
                <form method="POST" action="{{ route('seller.orders.handedOver', $order->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-primary"><i class="bi bi-box-arrow-right me-1"></i>Mark Handed Over</button>
                </form>
            @endif
            @if($order->conversation_id)
                <a href="{{ route('conversations.show', $order->conversation_id) }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-chat-dots me-1"></i>Message Buyer
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
