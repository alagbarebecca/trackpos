@extends('layouts.app')
@section('title', 'Profit/Loss')
@section('page-title', 'Profit & Loss Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Profit & Loss Report</h4>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Sales</h6>
                    <h3 class="text-success">${{ number_format($totalSales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Cost</h6>
                    <h3 class="text-danger">${{ number_format($totalCost, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Net Profit</h6>
                    <h3 class="text-{{ $profit >= 0 ? 'success' : 'danger' }}">${{ number_format($profit, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
