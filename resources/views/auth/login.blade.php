<!DOCTYPE html>
<html lang="id" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }} - Klinik</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v3.0.0/css/line.css">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon.png') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            overflow: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-animation .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
            animation: float 15s infinite ease-in-out;
        }

        .bg-animation .shape:nth-child(1) {
            width: 400px;
            height: 400px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .bg-animation .shape:nth-child(2) {
            width: 300px;
            height: 300px;
            top: 50%;
            right: -80px;
            animation-delay: -5s;
        }

        .bg-animation .shape:nth-child(3) {
            width: 500px;
            height: 500px;
            bottom: -150px;
            left: 40%;
            animation-delay: -10s;
        }

        .bg-animation .shape:nth-child(4) {
            width: 200px;
            height: 200px;
            top: 30%;
            left: 20%;
            animation-delay: -7s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-40px) rotate(180deg);
            }
        }

        /* Left Panel - Branding */
        .left-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
            z-index: 1;
        }

        .brand-content {
            text-align: center;
            color: #fff;
        }

        .brand-logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 50px;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }

        .brand-title {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #00f5a0, #00d9f5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            max-width: 400px;
            line-height: 1.6;
        }

        .features {
            margin-top: 50px;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.8);
        }

        .feature-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #00f5a0;
        }

        /* Right Panel - Login Form */
        .right-panel {
            width: 500px;
            min-height: 100vh;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            z-index: 1;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 16px 18px 16px 50px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 18px;
        }

        .toggle-password:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #00f5a0;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Social Login */
        .btn-google {
            width: 100%;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-google:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .btn-google img {
            width: 22px;
            height: 22px;
        }

        /* Register Link */
        .register-link {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        .register-link a {
            color: #00f5a0;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Error Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .text-danger {
            color: #fca5a5;
            font-size: 13px;
            margin-top: 8px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .left-panel {
                display: none;
            }

            .right-panel {
                width: 100%;
                max-width: 500px;
                margin: 0 auto;
                border: none;
                background: transparent;
            }
        }

        @media (max-width: 576px) {
            .right-panel {
                padding: 40px 25px;
            }

            .brand-title {
                font-size: 32px;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Left Panel - Branding -->
    <div class="left-panel">
        <div class="brand-content">
            <div class="brand-logo">
                <i class="uil uil-clinic-medical"></i>
            </div>
            <h1 class="brand-title">Klinik</h1>
            <p class="brand-subtitle">Sistem Manajemen Klinik Modern untuk mengelola pasien, antrian, rekam medis, dan
                pembayaran dengan mudah.</p>

            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon"><i class="uil uil-users-alt"></i></div>
                    <span>Manajemen Pasien Terintegrasi</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="uil uil-clock"></i></div>
                    <span>Sistem Antrian Otomatis</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="uil uil-file-medical"></i></div>
                    <span>Rekam Medis Digital</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="uil uil-chart-line"></i></div>
                    <span>Laporan & Statistik Real-time</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="right-panel">
        <div class="login-header">
            <h2>Selamat Datang!</h2>
            <p>Silakan masuk ke akun Anda</p>
        </div>

        @if(session('message'))
            <div
                class="alert {{ str_contains(session('message'), 'failed') || str_contains(session('message'), 'belum') ? 'alert-danger' : 'alert-success' }}">
                {{ session('message') }}
            </div>
        @endif

        <form action="{{ route('authenticate') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="uil uil-envelope"></i>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda"
                        value="{{ old('email') }}">
                </div>
                @if($errors->has('email'))
                    <p class="text-danger">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <div class="input-wrapper">
                    <i class="uil uil-lock"></i>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Masukkan kata sandi">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="uil uil-eye-slash" id="toggleIcon"></i>
                    </button>
                </div>
                @if($errors->has('password'))
                    <p class="text-danger">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <div class="form-options">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember">
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('forget_password') }}" class="forgot-link">Lupa kata sandi?</a>
            </div>

            <button type="submit" class="btn-login">
                <i class="uil uil-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="divider">atau masuk dengan</div>

        <a href="{{ route('auth.google') }}" class="btn-google">
            <svg width="22" height="22" viewBox="0 0 24 24">
                <path fill="#4285F4"
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                <path fill="#34A853"
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                <path fill="#FBBC05"
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                <path fill="#EA4335"
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            Masuk dengan Google
        </a>

        <p class="register-link">
            Belum punya akun?
            <a href="{{ route('register') }}">Daftar sekarang</a>
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('uil-eye-slash');
                toggleIcon.classList.add('uil-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('uil-eye');
                toggleIcon.classList.add('uil-eye-slash');
            }
        }
    </script>
</body>

</html>