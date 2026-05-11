@extends('layouts.app')

@section('title', 'Employee Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Employee: {{ $employee->user->name ?? 'N/A' }}</h4>
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Designation:</strong> {{ $employee->designation }}</p>
                            <p><strong>Hourly Rate:</strong> ${{ number_format($employee->hourly_rate ?? 0, 2) }}</p>
                            <p><strong>Hire Date:</strong> {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('Y-m-d') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Hours (This Month):</strong> {{ number_format($totalHours, 2) }}</p>
                            <p><strong>Total Pay (This Month):</strong> ${{ number_format($totalPay, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Recent Shifts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->date }}</td>
                                    <td>{{ $shift->clock_in ?? '-' }}</td>
                                    <td>{{ $shift->clock_out ?? '-' }}</td>
                                    <td>{{ number_format($shift->hours_worked ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No shifts this month</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection