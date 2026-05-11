@extends('layouts.app')
@section('title', 'Time Clock')
@section('page-title', 'Time Clock')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Time Clock</h4>
                </div>
                <div class="card-body text-center py-5">
                    @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                    @endif

                    @if($activeShift)
                    <div class="mb-4">
                        <div class="display-4 text-success mb-3">
                            <i class="fas fa-stopwatch"></i>
                        </div>
                        <h5 class="text-success">Currently Working</h5>
                        <p class="text-muted">Clocked in: {{ $activeShift->clock_in->format('g:i A') }}</p>
                        <p class="lead">Started at {{ $activeShift->clock_in->format('M d, Y g:i A') }}</p>
                    </div>
                    
                    <form action="{{ route('staff.clock-out') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-lg px-5">
                            <i class="fas fa-sign-out-alt me-2"></i>Clock Out
                        </button>
                    </form>
                    @else
                    <div class="mb-4">
                        <div class="display-4 text-secondary mb-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>Ready to Work?</h5>
                        <p class="text-muted">{{ now()->format('l, F d, Y') }}</p>
                    </div>
                    
                    <form action="{{ route('staff.clock-in') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-sign-in-alt me-2"></i>Clock In
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Today's Staff</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        View schedule at <a href="{{ route('staff.schedule') }}">Schedule</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection