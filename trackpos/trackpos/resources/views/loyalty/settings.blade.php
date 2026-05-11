@extends('layouts.app')
@section('title', 'Loyalty Settings')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-cog me-2" style="color: #7c3aed;"></i>Loyalty Program Settings</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        @csrf
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="loyalty_enabled" class="form-check-input" id="loyaltyEnabled" value="1" {{ Setting::get('loyalty_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="loyaltyEnabled">Enable Loyalty Program</label>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label">Points Earned Per Dollar Spent</label>
                            <input type="number" name="points_per_dollar" class="form-control" value="{{ Setting::get('loyalty_points_per_dollar') ?? 1 }}" step="0.1" min="0.1">
                            <small class="text-muted">Customers earn this many points for every $1 spent</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Point Redemption Value</label>
                            <input type="number" name="redemption_value" class="form-control" value="{{ Setting::get('loyalty_redemption_value') ?? 0.01 }}" step="0.01" min="0.01">
                            <small class="text-muted">How much $1 is worth in points (e.g., 100 pts = $1)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Minimum Points to Redeem</label>
                            <input type="number" name="min_redeem" class="form-control" value="{{ Setting::get('loyalty_min_redeem') ?? 100 }}">
                            <small class="text-muted">Minimum points required before redemption</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection