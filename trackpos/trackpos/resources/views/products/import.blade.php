@extends('layouts.app')
@section('title', 'Import Products')
@section('page-title', 'Bulk Import Products')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-import me-2"></i>Import Products from CSV</h5>
                </div>
                <div class="card-body">
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
                    
                    <div class="mb-4">
                        <h6>Instructions:</h6>
                        <ol class="text-muted">
                            <li>Download the template CSV file to see the required format</li>
                            <li>Fill in your product data in the CSV file</li>
                            <li>Upload the file and click "Import"</li>
                            <li>Existing products (matched by code) will be updated, new products will be created</li>
                        </ol>
                    </div>
                    
                    <div class="mb-4">
                        <a href="{{ route('products.import.template') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Download Template
                        </a>
                    </div>
                    
                    <form method="POST" action="{{ route('products.import.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select CSV File</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            <small class="text-muted">Maximum file size: 2MB</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Supported Columns:</label>
                            <ul class="text-muted small">
                                <li><strong>name</strong> (required) - Product name</li>
                                <li><strong>code</strong> (optional) - SKU/Product code</li>
                                <li><strong>barcode</strong> (optional) - Barcode number</li>
                                <li><strong>category</strong> (optional) - Category name (will create if not exists)</li>
                                <li><strong>unit</strong> (optional) - Unit name (will create if not exists)</li>
                                <li><strong>brand</strong> (optional) - Brand name (will create if not exists)</li>
                                <li><strong>cost_price</strong> (optional) - Cost/Purchase price</li>
                                <li><strong>sell_price</strong> (optional) - Selling price</li>
                                <li><strong>stock</strong> (optional) - Initial stock quantity</li>
                                <li><strong>alert_quantity</strong> (optional) - Low stock alert level</li>
                            </ul>
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Import Products
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection