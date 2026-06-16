<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Halolets Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --hl-blue:        #3B63E8;
            --hl-blue-dark:   #2947C8;
            --hl-blue-deeper: #1E3499;
            --hl-blue-light:  #EEF2FF;
            --hl-yellow:      #F7C419;
            --hl-yellow-dark: #D4A800;
        }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(145deg, var(--hl-blue-deeper) 0%, var(--hl-blue) 55%, #5B7FF0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: .85rem;
            box-shadow: 0 18px 48px rgba(26, 42, 108, .28);
            overflow: hidden;
        }

        .login-header {
            background: #fff;
            padding: 2rem 2rem 1.25rem;
            text-align: center;
            border-bottom: 1px solid #DDE3F0;
        }

        .hl-logo {
            height: 56px;
            width: auto;
            margin-bottom: .75rem;
        }

        .login-header h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1A2A6C;
            margin: 0 0 .25rem;
        }

        .login-header p {
            margin: 0;
            font-size: .875rem;
            color: #6B7280;
        }

        .login-body {
            background: #fff;
            padding: 1.5rem 2rem 2rem;
        }

        .form-label {
            font-size: .84rem;
            font-weight: 600;
            color: #1A2A6C;
        }

        .form-control:focus {
            border-color: var(--hl-blue);
            box-shadow: 0 0 0 .2rem rgba(59, 99, 232, .18);
        }

        .btn-sign-in {
            background: var(--hl-blue);
            border-color: var(--hl-blue);
            font-weight: 600;
            padding: .6rem 1rem;
        }

        .btn-sign-in:hover {
            background: var(--hl-blue-dark);
            border-color: var(--hl-blue-dark);
        }

        .login-footer {
            background: var(--hl-blue-light);
            padding: .85rem 2rem;
            text-align: center;
            font-size: .78rem;
            color: var(--hl-blue);
            border-top: 1px solid #D0DAFF;
        }
    </style>
</head>
<body>

<div class="login-card card">
    <div class="login-header">
        <img src="/images/halolets-logo.png" alt="Halolets" class="hl-logo">
        <h1>Admin Portal</h1>
        <p>Sign in to continue</p>
    </div>

    <div class="login-body">
        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="form-check mb-4">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    class="form-check-input"
                    {{ old('remember') ? 'checked' : '' }}
                >
                <label for="remember" class="form-check-label">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-sign-in w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
            </button>
        </form>
    </div>

    <div class="login-footer">
        Halolets internal use only
    </div>
</div>

</body>
</html>
