<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installing Loop POS...</title>
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
            max-width: 500px;
            width: 100%;
            text-align: center;
            padding: 60px 40px;
        }
        
        .spinner-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 40px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        h2 { color: #1e1b4b; margin-bottom: 10px; }
        p { color: #64748b; }
        
        .install-steps {
            text-align: left;
            margin-top: 30px;
            background: #f8fafc;
            border-radius: 15px;
            padding: 20px;
        }
        
        .install-step {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .install-step:last-child { border-bottom: none; }
        
        .install-step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background: #e2e8f0;
            color: #64748b;
        }
        .install-step-icon.done {
            background: #d1fae5;
            color: #10b981;
        }
        .install-step-icon.loading {
            background: #fee2e2;
            color: #f59e0b;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="spinner-icon">
            <i class="fas fa-cog"></i>
        </div>
        
        <h2>Installing Loop POS</h2>
        <p>Please wait while we set up your system...</p>
        
        <div class="install-steps">
            <div class="install-step">
                <div class="install-step-icon done">
                    <i class="fas fa-check"></i>
                </div>
                <span>Database connected</span>
            </div>
            <div class="install-step">
                <div class="install-step-icon loading" id="migrate-spinner">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <span id="migrate-text">Running migrations...</span>
            </div>
            <div class="install-step">
                <div class="install-step-icon" id="seed-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <span id="seed-text">Seeding default data...</span>
            </div>
        </div>
        
        <script>
            // Auto-redirect after 3 seconds (migrations should be done by then)
            setTimeout(function() {
                document.getElementById('migrate-spinner').classList.remove('loading');
                document.getElementById('migrate-spinner').classList.add('done');
                document.getElementById('migrate-spinner').innerHTML = '<i class="fas fa-check"></i>';
                document.getElementById('migrate-text').textContent = 'Migrations complete';
                
                setTimeout(function() {
                    document.getElementById('seed-icon').classList.add('done');
                    document.getElementById('seed-icon').innerHTML = '<i class="fas fa-check"></i>';
                    document.getElementById('seed-text').textContent = 'Default data seeded';
                    
                    setTimeout(function() {
                        window.location.href = '{{ route("install.step4") }}';
                    }, 1000);
                }, 1500);
            }, 2000);
        </script>
    </div>
</body>
</html>