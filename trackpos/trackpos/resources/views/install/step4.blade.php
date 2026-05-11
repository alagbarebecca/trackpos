<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Loop POS - Create Admin</title>
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
        .install-header h1 { margin: 0; font-size: 2rem; }
        .install-header p { opacity: 0.8; margin: 10px 0 0 0; }
        
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
        .step.active { color: #7c3aed; }
        .step.completed { color: #10b981; }
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
        .step.active .step-number, .step.completed .step-number {
            background: currentColor;
            color: white;
        }
        .step-label { font-size: 14px; }
        .step-divider { width: 40px; height: 2px; background: #e2e8f0; margin: 0 10px; }
        
        .install-body { padding: 40px; }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
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
        .btn-install:hover { transform: translateY(-2px); }
        
        .user-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
        }
        
        .success-box {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="install-header">
            <div class="user-icon"><i class="fas fa-user-shield"></i></div>
            <h1>Create Admin Account</h1>
            <p>Set up your administrator account</p>
        </div>
        
        <div class="progress-steps">
            <div class="step completed">
                <span class="step-number"><i class="fas fa-check"></i></span>
                <span class="step-label">Requirements</span>
            </div>
            <div class="step-divider"></div>
            <div class="step completed">
                <span class="step-number"><i class="fas fa-check"></i></span>
                <span class="step-label">Database</span>
            </div>
            <div class="step-divider"></div>
            <div class="step completed">
                <span class="step-number"><i class="fas fa-check"></i></span>
                <span class="step-label">Install</span>
            </div>
            <div class="step-divider"></div>
            <div class="step active">
                <span class="step-number">4</span>
                <span class="step-label">Admin</span>
            </div>
        </div>

        <div class="install-body">
            @if($adminExists)
            <div class="success-box">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <h4>Installation Complete!</h4>
                <p class="text-muted">Your Loop POS system is ready to use.</p>
                <a href="{{ route('login') }}" class="btn btn-install mt-3">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Go to Login
                </a>
            </div>
            @else
            <form method="POST" action="{{ route('install.complete') }}">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="admin">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Administrator">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="admin@example.com">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 characters">
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Confirm password">
                </div>
                
                <button type="submit" class="btn btn-install">
                    <i class="fas fa-check me-2"></i>
                    Complete Installation
                </button>
            </form>
            @endif
        </div>
    </div>
</body>
</html>