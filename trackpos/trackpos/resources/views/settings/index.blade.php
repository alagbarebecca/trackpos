@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Application Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                
                <!-- General Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Application Name</label>
                                <input type="text" class="form-control" name="app_name" value="{{ $settings['app_name'] ?? 'TrackPOS' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? '$' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default Tax Rate (%)</label>
                                <input type="number" class="form-control" name="default_tax_rate" value="{{ $settings['default_tax_rate'] ?? 0 }}" min="0" max="100" step="0.01">
                                <small class="text-muted">Applied automatically to sales</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Language</label>
                                <select class="form-select" name="language">
                                    <option value="English" {{ ($settings['language'] ?? 'English') == 'English' ? 'selected' : '' }}>English</option>
                                    <option value="Spanish" {{ ($settings['language'] ?? '') == 'Spanish' ? 'selected' : '' }}>Spanish</option>
                                    <option value="French" {{ ($settings['language'] ?? '') == 'French' ? 'selected' : '' }}>French</option>
                                    <option value="Portuguese" {{ ($settings['language'] ?? '') == 'Portuguese' ? 'selected' : '' }}>Portuguese</option>
                                    <option value="Arabic" {{ ($settings['language'] ?? '') == 'Arabic' ? 'selected' : '' }}>Arabic</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Company Details (for Receipts)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" name="company_name" value="{{ $settings['company_name'] ?? '' }}" placeholder="Your Company Name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax ID / VAT Number</label>
                                <input type="text" class="form-control" name="company_tax_id" value="{{ $settings['company_tax_id'] ?? '' }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="company_address" rows="2">{{ $settings['company_address'] ?? '' }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="company_phone" value="{{ $settings['company_phone'] ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="company_email" value="{{ $settings['company_email'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Customization -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Customization</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Logo URL</label>
                                <input type="text" class="form-control" name="invoice_logo" value="{{ $settings['invoice_logo'] ?? '' }}" placeholder="https://example.com/logo.png">
                                <small class="text-muted">Enter URL to your logo image</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Footer Text</label>
                                <textarea class="form-control" name="invoice_footer" rows="2" placeholder="Thank you for your business!">{{ $settings['invoice_footer'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount Rules -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-percent me-2"></i>Discount Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Default Discount Type</label>
                                <select class="form-select" name="default_discount_type">
                                    <option value="percentage" {{ ($settings['default_discount_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed" {{ ($settings['default_discount_type'] ?? '') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Default Discount Value</label>
                                <input type="number" class="form-control" name="default_discount_value" value="{{ $settings['default_discount_value'] ?? 0 }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bulk Discount Threshold (Qty)</label>
                                <input type="number" class="form-control" name="bulk_discount_threshold" value="{{ $settings['bulk_discount_threshold'] ?? 10 }}" min="1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bulk Discount %</label>
                                <input type="number" class="form-control" name="bulk_discount_percent" value="{{ $settings['bulk_discount_percent'] ?? 5 }}" min="0" max="100">
                            </div>
                        </div>
                        <small class="text-muted">When customer buys more than the threshold quantity of an item, the bulk discount % will be applied automatically.</small>
                    </div>
                </div>

                <!-- Receipt Preview -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Receipt Preview</h5>
                    </div>
                    <div class="card-body">
                        <div class="border rounded p-3" style="max-width: 300px; margin: 0 auto; font-family: 'Courier New', monospace; font-size: 12px;">
                            <div class="text-center mb-2">
                                <strong>{{ $settings['company_name'] ?? 'Your Company Name' }}</strong><br>
                                @if($settings['company_address'] ?? '')
                                <small>{{ $settings['company_address'] }}</small><br>
                                @endif
                                @if($settings['company_phone'] ?? '')
                                <small>Tel: {{ $settings['company_phone'] }}</small><br>
                                @endif
                                @if($settings['company_email'] ?? '')
                                <small>{{ $settings['company_email'] }}</small><br>
                                @endif
                                @if($settings['company_tax_id'] ?? '')
                                <small>Tax ID: {{ $settings['company_tax_id'] }}</small><br>
                                @endif
                            </div>
                            <div class="border-top border-bottom py-1 my-2">
                                <small>Sample Receipt - INV-00001</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Item x1</span>
                                <span>{{ $settings['currency_symbol'] ?? '$' }}10.00</span>
                            </div>
                            <div class="border-top mt-1 pt-1 d-flex justify-content-between">
                                <span><strong>TOTAL</strong></span>
                                <span><strong>{{ $settings['currency_symbol'] ?? '$' }}10.00</strong></span>
                            </div>
                            <div class="text-center mt-2">
                                <small>Thank you for your purchase!</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection