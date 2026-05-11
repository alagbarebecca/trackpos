@extends('layouts.app')
@section('title', 'Returns')
@section('page-title', 'Product Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Returns</h4>
        <a href="{{ route('returns.create') }}" class="btn btn-primary">
            <i class="fas fa-undo me-2"></i>New Return
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Refund</th>
                            <th>Method</th>
                            <th>User</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr>
                            <td><span class="badge bg-info">{{ $return->return_number }}</span></td>
                            <td>{{ $return->created_at->format('M d, Y') }}</td>
                            <td>{{ $return->sale->invoice_no }}</td>
                            <td>{{ $return->product->name }}</td>
                            <td>{{ $return->quantity }}</td>
                            <td class="text-danger">-${{ number_format($return->refund_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $return->refund_method == 'cash' ? 'success' : 'warning' }}">
                                    {{ ucfirst($return->refund_method) }}
                                </span>
                            </td>
                            <td>{{ $return->user->name }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $return->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <div class="modal fade" id="detailModal{{ $return->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Return Details - {{ $return->return_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-4">Date</dt>
                                            <dd class="col-sm-8">{{ $return->created_at->format('M d, Y H:i:s') }}</dd>
                                            <dt class="col-sm-4">Invoice</dt>
                                            <dd class="col-sm-8">{{ $return->sale->invoice_no }}</dd>
                                            <dt class="col-sm-4">Product</dt>
                                            <dd class="col-sm-8">{{ $return->product->name }}</dd>
                                            <dt class="col-sm-4">Quantity</dt>
                                            <dd class="col-sm-8">{{ $return->quantity }}</dd>
                                            <dt class="col-sm-4">Unit Price</dt>
                                            <dd class="col-sm-8">${{ number_format($return->unit_price, 2) }}</dd>
                                            <dt class="col-sm-4">Refund Amount</dt>
                                            <dd class="col-sm-8 text-danger">${{ number_format($return->refund_amount, 2) }}</dd>
                                            <dt class="col-sm-4">Refund Method</dt>
                                            <dd class="col-sm-8">{{ ucfirst($return->refund_method) }}</dd>
                                            <dt class="col-sm-4">Reason</dt>
                                            <dd class="col-sm-8">{{ $return->reason ?? 'N/A' }}</dd>
                                            <dt class="col-sm-4">Processed By</dt>
                                            <dd class="col-sm-8">{{ $return->user->name }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No returns found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection