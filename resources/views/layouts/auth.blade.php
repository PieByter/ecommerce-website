<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Autentikasi')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/js/app.js'])

    <style>
        :root {
            --primary: #F76722;
            --primary-dark: #E55A1F;
            --dark: #1a1f2e;
            --light: #f0f2f5;
            --text-color: #1a1f2e;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--light) 0%, #ffffff 50%, var(--light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: var(--text-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .auth-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(26, 31, 46, 0.15);
            overflow: hidden;
            background: var(--primary);
        }

        .auth-brand {
            background: var(--primary);
            color: #fff;
            padding: 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-brand i {
            font-size: 1.5rem;
        }

        .auth-card .card-body {
            background: #fff;
            padding: 2rem !important;
        }

        .form-label {
            color: var(--text-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.6rem 0.75rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(247, 103, 34, 0.15);
        }

        .btn-auth {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
        }

        .btn-auth:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #fff;
        }

        .auth-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 540px;">
        <div class="card auth-card">
            <div class="auth-brand">
                <i class="bi bi-gear-fill me-2"></i>Toko Sparepart Motor
            </div>
            <div class="card-body p-4 p-md-5">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
