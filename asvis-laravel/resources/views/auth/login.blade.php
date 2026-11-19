<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
</head>
<body>

    <x-navbar />

    <main class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-aside">
                <div class="auth-aside-brand">
                    <h2>ASVIS</h2>
                    <p class="tagline">Alimentation Saine, Vie Saine</p>
                    <p class="desc">
                        Platform asisten kesehatan cerdas berbasis AI untuk memantau nutrisi harian, aktivitas olahraga, dan kualitas udara secara real-time.
                    </p>
                </div>

                <div class="auth-aside-badges">
                    <div class="auth-aside-badge" title="Food Tracker"><i class="fa-solid fa-leaf"></i></div>
                    <div class="auth-aside-badge" title="Exercise & AQI"><i class="fa-solid fa-person-biking"></i></div>
                    <div class="auth-aside-badge" title="Health Score"><i class="fa-solid fa-heart-pulse"></i></div>
                </div>
            </div>

            <div class="auth-form-panel">
                <h1>Welcome Back</h1>
                <p class="subtitle">Masuk ke akun ASVIS kamu untuk melanjutkan catatan hari ini.</p>

                @if (session('status'))
                    <div class="auth-status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus autocomplete="username">
                        @error('email')
                            <div class="auth-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        @error('password')
                            <div class="auth-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-options">
                        <label for="remember_me" class="auth-remember">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="auth-link" href="{{ route('password.request') }}">Lupa password?</a>
                        @endif
                    </div>

                    <button type="submit" class="auth-submit-btn">Masuk</button>
                </form>

                <div class="auth-switch">
                    Belum punya akun? <a href="{{ route('register') }}" class="auth-link">Daftar sekarang</a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>