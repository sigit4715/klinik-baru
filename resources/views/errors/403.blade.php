<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background: #f4f5f7; color: #1f2937;
            display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 40px 48px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.06);
            text-align: center; max-width: 440px; }
        h1 { font-size: 56px; margin: 0; color: #ef4444; }
        p { color: #6b7280; }
        a { display: inline-block; margin-top: 12px; padding: 10px 20px; background: #5f63f2; color: #fff;
            border-radius: 6px; text-decoration: none; }
    </style>
</head>

<body>
    <div class="box">
        <h1>403</h1>
        <h2>Akses ditolak</h2>
        <p>{{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}</p>
        <p>Jika Anda membutuhkan akses, hubungi Administrator untuk menambahkan hak akses pada role Anda.</p>
        @auth
            <a href="{{ route('clinic.dashboard', ['language' => app()->getLocale() ?: 'en']) }}">Kembali ke Dashboard</a>
        @else
            <a href="{{ route('login') }}">Login</a>
        @endauth
    </div>
</body>

</html>
