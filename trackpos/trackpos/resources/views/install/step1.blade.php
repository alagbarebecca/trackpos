<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Loop POS - System Requirements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 50%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .install-header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .install-header p {
            opacity: 0.8;
            margin: 10px 0 0 0;
        }
        .progress-steps {
            display: flex;
            justify-content: center;
            padding: 20px;
            background: #f8fafc;
        }
        .step {
            display: flex;
            align-items: center;
            color: #cbd5e1;
        }
        .step.active {
            color: #7c3aed;
        }
        .step.completed {
            color: #10b981;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid currentColor;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-weight: bold;
            font-size: 14px;
        }
        .step.active .step-number,
        .step.completed .step-number {
            background: currentColor;
            color: white;
        }
        .step-label {
            font-size: 14px;
        }
        .step-divider {
            width: 40px;
            height: 2px;
            background: #e2e8f0;
            margin: 0 10px;
        }
        .install-body {
            padding: 40px;
        }
        .req-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: #f8fafc;
        }
        .req-item.pass {
            background: #ecfdf5;
        }
        .req-item.fail {
            background: #fef2f2;
        }
        .req-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .req-item.pass .req-icon {
            background: #d1fae5;
            color: #10b981;
        }
        .req-item.fail .req-icon {
            background: #fee2e2;
            color: #ef4444;
        }
        .btn-install {
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-install:hover:not(:disabled) {
            transform: translateY(-2px);
        }
        .btn-install:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="install-header">
            <i class="fas fa-cash-register fa-3x mb-3"></i>
            <h1>Loop POS</h1>
            <p>Point of Sale System</p>
        </div>
        
        <div class="progress-steps">
            <div class="step completed">
                <span class="step-number"><i class="fas fa-check"></i></span>
                <span class="step-label">Requirements</span>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <span class="step-number">2</span>
                <span class="step-label">Database</span>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <span class="step-number">3</span>
                <span class="step-label">Install</span>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <span class="step-number">4</span>
                <span class="step-label">Admin</span>
            </div>
        </div>

        <div class="install-body">
            <h4 class="mb-4"><i class="fas fa-server me-2" style="color: #7c3aed;"></i>System Requirements</h4>
            
            @foreach($requirements as $key => $req)
            <div class="req-item {{ $req['status'] ? 'pass' : 'fail' }}">
                <div class="d-flex align-items-center">
                    <div class="req-icon">
                        <i class="fas {{ $req['status'] ? 'fa-check' : 'fa-times' }}"></i>
                    </div>
                    <div>
                        <strong>{{ $req['name'] }}</strong>
                        @if(!$req['status'])
                        <br><small class="text-danger">{{ $req['message'] }}</small>
                        @endif
                    </div>
                </div>
                <i class="fas {{ $req['status'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} fa-lg"></i>
            </div>
            @endforeach

            <div class="mt-4">
                <a href="{{ $allPassed ? route('install.step2') : '#' }}" 
                   class="btn btn-install {{ !$allPassed ? 'disabled' : '' }}">
                    <i class="fas fa-arrow-right me-2"></i>
                    {{ $allPassed ? 'Continue to Database Setup' : 'Fix Requirements to Continue' }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>