@extends('layouts.app')
@section('title', 'Units')
@section('page-title', 'Unit Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Unit Management</h4>
        <button class="btn" style="background: #7c3aed; color: #fff; border: none;" data-bs-toggle="modal" data-bs-target="#addUnitModal">
            <i class="fas fa-plus me-2"></i>Add Unit
        </button>
    </div>

    <!-- Units Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-balance-scale me-2" style="color: #7c3aed;"></i>Available Units</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Unit Name</th>
                            <th>Short Name</th>
                            <th>Products Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr>
                            <td class="fw-semibold">{{ $unit->name }}</td>
                            <td><span class="badge" style="background: #7c3aed; color: #fff;">{{ $unit->short_name }}</span></td>
                            <td>{{ $unit->products()->count() }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure? This will delete the unit.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editUnitModal{{ $unit->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('units.update', $unit) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff;">
                                            <h5 class="modal-title">Edit Unit</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Unit Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $unit->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Short Name</label>
                                                <input type="text" name="short_name" class="form-control" value="{{ $unit->short_name }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Update</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-balance-scale fa-2x mb-2 d-block opacity-50"></i>
                                No units found. Create one to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($units->hasPages())
        <div class="card-footer">
            {{ $units->links() }}
        </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="card mt-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
        <div class="card-body">
            <h6><i class="fas fa-info-circle me-2" style="color: #7c3aed;"></i>About Units</h6>
            <p class="text-muted mb-0">
                Units help you measure products in different ways. Common examples include:
            </p>
            <div class="mt-2">
                <span class="badge bg-secondary me-1">Kilogram (kg)</span>
                <span class="badge bg-secondary me-1">Gram (g)</span>
                <span class="badge bg-secondary me-1">Liter (L)</span>
                <span class="badge bg-secondary me-1">Milliliter (ml)</span>
                <span class="badge bg-secondary me-1">Cup</span>
                <span class="badge bg-secondary me-1">Piece (pcs)</span>
                <span class="badge bg-secondary me-1">Pack</span>
                <span class="badge bg-secondary me-1">Box</span>
            </div>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%); color: #fff;">
                    <h5 class="modal-title">Add New Unit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Kilogram, Liter, Cup" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short Name *</label>
                        <input type="text" name="short_name" class="form-control" placeholder="e.g., kg, L, cup" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" style="background: #7c3aed; color: #fff; border: none;">Save Unit</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection