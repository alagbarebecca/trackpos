@extends('layouts.app')
@section('title', 'Import / Export')
@section('page-title', 'Data Import / Export')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Products -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Products</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('import-export.products') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-download me-2"></i>Export Products CSV
                        </a>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('import-export.template') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-file-download me-2"></i>Download Template
                        </a>
                    </div>
                    <hr>
                    <form action="{{ route('import-export.products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Import Products</label>
                            <input type="file" name="file" class="form-control" accept=".csv" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mode</label>
                            <select name="mode" class="form-select">
                                <option value="both">Create & Update</option>
                                <option value="create">Create New Only</option>
                                <option value="update">Update Existing Only</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-upload me-2"></i>Import CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sales -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Sales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('import-export.sales') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-download me-2"></i>Export Sales CSV
                        </a>
                    </div>
                    <p class="text-muted small">Download all sales records to CSV for analysis.</p>
                </div>
            </div>
        </div>

        <!-- Customers -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Customers</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('import-export.customers') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-download me-2"></i>Export Customers CSV
                        </a>
                    </div>
                    <p class="text-muted small">Download all customer records to CSV.</p>
                </div>
            </div>
        </div>

        <!-- Templates -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-code me-2"></i>CSV Format</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2"><strong>Columns:</strong></p>
                    <code class="d-block mb-2">id, name, sku, barcode, category, brand, cost_price, sell_price, stock_quantity, alert_quantity, unit, status</code>
                    <p class="text-muted small mb-0"><strong>Notes:</strong></p>
                    <ul class="text-muted small">
                        <li>Leave id empty to create new</li>
                        <li>Use SKU to match existing</li>
                        <li>status: active or inactive</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection