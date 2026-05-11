<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Loop POS - Database Configuration</title>
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
        
        .db-icon {
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
        
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 10px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="install-header">
            <div class="db-icon"><i class="fas fa-database"></i></div>
            <h1>Database Configuration</h1>
            <p>Enter your MySQL database details</p>
        </div>
        
        <div class="progress-steps">
            <div class="step completed">
                <span class="step-number"><i class="fas fa-check"></i></span>
                <span class="step-label">Requirements</span>
            </div>
            <div class="step-divider"></div>
            <div class="step active">
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
            @if(session('error'))
            <div class="alert-danger mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
            @endif
            
            <form method="POST" action="{{ route('install.step3') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" name="DB_HOST" class="form-control" value="{{ old('DB_HOST', $dbConfig['DB_HOST']) }}" placeholder="127.0.0.1 (leave empty for SQLite)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Port</label>
                        <input type="text" name="DB_PORT" class="form-control" value="{{ old('DB_PORT', $dbConfig['DB_PORT']) }}" placeholder="3306">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Database Name</label>
                    <input type="text" name="DB_DATABASE" class="form-control" value="{{ old('DB_DATABASE', $dbConfig['DB_DATABASE']) }}" placeholder="loop_pos or database.sqlite">
                    <small class="text-muted">For SQLite, use path like database/database.sqlite</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="DB_USERNAME" class="form-control" value="{{ old('DB_USERNAME', $dbConfig['DB_USERNAME']) }}" placeholder="root (leave empty for SQLite)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="DB_PASSWORD" class="form-control" value="{{ old('DB_PASSWORD', $dbConfig['DB_PASSWORD']) }}" placeholder="Leave empty if no password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-install">
                    <i class="fas fa-plug me-2"></i>
                    Test & Continue
                </button>
            </form>
        </div>
    </div>
</body>
</html>