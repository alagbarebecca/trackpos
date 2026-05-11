@extends('layouts.app')
@section('title', 'Daily Sales')
@section('page-title', "Today's Sales")

<style>
@media print {
    .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0">Daily Sales - {{ $date }}</h4>
        <div>
            <a href="{{ route('reports.export-daily', ['date' => $date]) }}" class="btn btn-info">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
            <a href="{{ route('reports.print-daily', ['date' => $date]) }}" target="_blank" class="btn btn-success">
                <i class="fas fa-print me-2"></i>Print / PDF
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Sales List</h5>
            <h5 class="mb-0">Total: {{ $currencySymbol ?? '$' }}{{ number_format($total, 2) }}</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_no }}</td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                        <td>{{ $currencySymbol ?? '$' }}{{ number_format($sale->total, 2) }}</td>
                        <td>{{ ucfirst($sale->payment_method) }}</td>
                        <td>{{ $sale->created_at->format('H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
