@extends('layouts.app')
@section('title', 'Audit Logs')
@section('page-title', 'Activity History')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="entity_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($entityTypes as $type)
                        <option value="{{ $type }}" {{ request('entity_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>
                                @if($log->user)
                                <span class="badge bg-primary">{{ $log->user->name }}</span>
                                @else
                                <span class="badge bg-secondary">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->action == 'deleted' ? 'danger' : ($log->action == 'created' ? 'success' : 'warning') }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td>
                                @if($log->entity_type)
                                <span class="text-muted">{{ class_basename($log->entity_type) }}</span>
                                @if($log->entity_id)
                                <span class="badge bg-light text-dark">#{{ $log->entity_id }}</span>
                                @endif
                                @endif
                            </td>
                            <td>{{ $log->description }}</td>
                            <td><small class="text-muted">{{ $log->ip_address ?? '-' }}</small></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Detail Modal -->
                        <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Activity Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-4">Date</dt>
                                            <dd class="col-sm-8">{{ $log->created_at->format('M d, Y H:i:s') }}</dd>
                                            
                                            <dt class="col-sm-4">User</dt>
                                            <dd class="col-sm-8">{{ $log->user?->name ?? 'System' }}</dd>
                                            
                                            <dt class="col-sm-4">Action</dt>
                                            <dd class="col-sm-8">{{ ucfirst($log->action) }}</dd>
                                            
                                            <dt class="col-sm-4">Entity</dt>
                                            <dd class="col-sm-8">{{ class_basename($log->entity_type) ?? '-' }} #{{ $log->entity_id }}</dd>
                                            
                                            <dt class="col-sm-4">IP Address</dt>
                                            <dd class="col-sm-8">{{ $log->ip_address ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-sm-4">Description</dt>
                                            <dd class="col-sm-8">{{ $log->description }}</dd>
                                        </dl>
                                        
                                        @if($log->old_values || $log->new_values)
                                        <hr>
                                        <h6>Changes</h6>
                                        @if($log->old_values)
                                        <div class="mb-2">
                                            <strong>Before:</strong>
                                            <pre class="small bg-light p-2">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                        @endif
                                        @if($log->new_values)
                                        <div>
                                            <strong>After:</strong>
                                            <pre class="small bg-light p-2">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                        @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No audit logs found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection