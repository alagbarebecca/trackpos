@extends('layouts.app')

@section('title', 'Weekly Schedule')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Weekly Schedule</h4>
                    <div>
                        <a href="{{ route('staff.index') }}" class="btn btn-secondary">Back to Staff</a>
                        <a href="{{ route('staff.schedule') }}" class="btn btn-primary">Day View</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-auto">
                            <a href="?start={{ \Carbon\Carbon::parse($startDate)->subWeek()->startOfWeek()->format('Y-m-d') }}" class="btn btn-outline-secondary">&laquo; Previous Week</a>
                        </div>
                        <div class="col-auto">
                            <span class="btn btn-outline-secondary disabled">
                                {{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="col-auto">
                            <a href="?start={{ \Carbon\Carbon::parse($startDate)->addWeek()->startOfWeek()->format('Y-m-d') }}" class="btn btn-outline-secondary">Next Week &raquo;</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                    <th>{{ $day }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts->groupBy('employee_id') as $employeeId => $employeeShifts)
                                <tr>
                                    <td>{{ $employeeShifts->first()->employee->user->name ?? 'N/A' }}</td>
                                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                    <td>
                                        @php
                                        $dayShifts = $employeeShifts->filter(function($s) use ($day) {
                                            return \Carbon\Carbon::parse($s->date)->format('l') == $day;
                                        });
                                        @endphp
                                        @foreach($dayShifts as $shift)
                                        <div class="small">{{ \Carbon\Carbon::parse($shift->date)->format('H:i') }}</div>
                                        @endforeach
                                    </td>
                                    @endforeach
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No scheduled shifts</td>
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