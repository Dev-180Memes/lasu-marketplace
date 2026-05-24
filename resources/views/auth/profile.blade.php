@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
<div class="container py-4" style="max-width:680px">
    <h4 class="fw-bold mb-4">My Profile</h4>

    <div class="card p-4 mb-4">
        <h6 class="fw-bold mb-3">Personal Information</h6>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PATCH')

            <div class="d-flex align-items-center gap-4 mb-4">
                <img src="{{ $user->avatar_url }}" class="rounded-circle"
                     width="72" height="72" style="object-fit:cover" id="avatarPreview">
                <div>
                    <label class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-camera me-1"></i>Change Photo
                        <input type="file" name="avatar" class="d-none" accept="image/*"
                               onchange="document.getElementById('avatarPreview').src=URL.createObjectURL(this.files[0])">
                    </label>
                    <div class="form-text">Max 2MB, square image recommended</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                <div class="form-text">Email cannot be changed.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">LASU Institutional Email</label>
                <input type="email" name="edu_email"
                       class="form-control @error('edu_email') is-invalid @enderror"
                       value="{{ old('edu_email', $user->edu_email) }}"
                       placeholder="yourname@lasu.edu.ng">
                @error('edu_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Faculty</label>
                    <input type="text" name="faculty" class="form-control"
                           value="{{ old('faculty', $user->faculty) }}" placeholder="e.g. Faculty of Science">
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Department</label>
                    <input type="text" name="department" class="form-control"
                           value="{{ old('department', $user->department) }}" placeholder="e.g. Computer Science">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="text" name="phone" class="form-control"
                       value="{{ old('phone', $user->phone) }}" placeholder="080xxxxxxxx">
            </div>

            <button class="btn btn-lasu px-4">Save Changes</button>
        </form>
    </div>

    <div class="card p-4">
        <h6 class="fw-bold mb-3">Change Password</h6>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PATCH')

            <div class="mb-3">
                <label class="form-label fw-semibold">Current Password</label>
                <input type="password" name="current_password"
                       class="form-control @error('current_password') is-invalid @enderror" required>
                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">New Password</label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button class="btn btn-outline-secondary px-4">Update Password</button>
        </form>
    </div>
</div>
@endsection
