<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $appName ?? 'TrackPOS' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f0d1a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header i { color: #a78bfa; }
        .login-body {
            padding: 40px;
        }
        .form-control {
            height: 48px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .btn-login {
            height: 48px;
            border-radius: 8px;
            background: #7c3aed;
            border: none;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #6d28d9;
        }
        .demo-accounts {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
        }
        .demo-accounts h6 {
            margin-bottom: 10px;
            color: #1e293b;
        }
        .demo-accounts code {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-cash-register fa-3x mb-3"></i>
            <h3 class="mb-0">{{ $appName ?? 'TrackPOS' }}</h3>
            <p class="mb-0 opacity-75">Point of Sale & Inventory</p>
        </div>
        <div class="login-body">
            <!-- Error Display -->
            @if($errors->any())
            <div class="alert alert-danger" style="border-radius: 8px; margin-bottom: 20px;">
                {{ $errors->first('login') }}
            </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <input type="hidden" name="_test" value="test_submit">
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="login" class="form-control" placeholder="Enter username or email" value="{{ old('login') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>
            </form>
            
            <script>
            document.getElementById('loginForm')?.addEventListener('submit', function(e) {
                console.log('Login form submitted');
                console.log('Login:', document.querySelector('[name="login"]').value);
            });
            </script>
            
            <div class="demo-accounts">
                <h6><i class="fas fa-info-circle me-2"></i>Your Login</h6>
                <code>Use your username or email created during setup</code>
            </div>
        </div>
    </div>
</body>
</html>
