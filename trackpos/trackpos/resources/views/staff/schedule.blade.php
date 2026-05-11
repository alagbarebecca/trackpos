@extends('layouts.app')
@section('title', 'Staff Schedule')
@section('page-title', 'Staff Schedule')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Staff Schedule</h4>
        <div>
            <a href="{{ route('staff.weekly-schedule') }}" class="btn btn-outline-primary">
                <i class="fas fa-calendar-week me-2"></i>Weekly View
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            @if($shifts->count() > 0)
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Duration</th>
                        <th>Break</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                    <tr>
                        <td>{{ $shift->employee->user->name ?? '-' }}</td>
                        <td>{{ $shift->clock_in?->format('g:i A') ?: '-' }}</td>
                        <td>{{ $shift->clock_out?->format('g:i A') ?: 'Working...' }}</td>
                        <td>{{ number_format($shift->hours_worked, 1) }} hrs</td>
                        <td>{{ $shift->break_minutes }} min</td>
                        <td>
                            @if($shift->clock_out)
                            <span class="badge bg-secondary">Completed</span>
                            @else
                            <span class="badge bg-success">On Shift</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-2x mb-2 d-block opacity-50"></i>
                No shifts scheduled for this date
            </div>
            @endif
        </div>
    </div>
</div>
@endsection