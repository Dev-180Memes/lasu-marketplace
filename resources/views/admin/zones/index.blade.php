@extends('layouts.app')
@section('title', 'Campus Zones')
@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Campus Zones</h4>
        <a href="{{ route('admin.zones.create') }}" class="btn btn-lasu">
            <i class="bi bi-plus-circle me-1"></i>Add Zone
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Zone Name</th>
                        <th>Description</th>
                        <th>Coordinates</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($zones as $zone)
                        <tr>
                            <td class="text-muted small">{{ $zone->id }}</td>
                            <td class="fw-semibold">{{ $zone->name }}</td>
                            <td class="small text-muted">{{ Str::limit($zone->description, 60) ?? '—' }}</td>
                            <td class="small text-muted">
                                @if($zone->latitude && $zone->longitude)
                                    {{ number_format($zone->latitude, 4) }},
                                    {{ number_format($zone->longitude, 4) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $zone->is_active ? 'success' : 'secondary' }}">
                                    {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.zones.edit', $zone->id) }}"
                                       class="btn btn-xs btn-outline-primary"
                                       style="font-size:.75rem;padding:2px 8px">Edit</a>
                                    <form method="POST" action="{{ route('admin.zones.destroy', $zone->id) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"
                                                style="font-size:.75rem;padding:2px 8px"
                                                onclick="return confirm('Delete this zone?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">No campus zones yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $zones->links() }}</div>
</div>
@endsection
