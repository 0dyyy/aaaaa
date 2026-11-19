<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Register</title>
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
                        Mulai perjalanan hidup sehatmu. Catat asupan makanan dengan AI, pantau kualitas udara saat berolahraga, dan raih skor kesehatan terbaikmu.
                    </p>
                </div>

                <div class="auth-aside-badges">
                    <div class="auth-aside-badge" title="Zero Hunger"><i class="fa-solid fa-bowl-food"></i></div>
                    <div class="auth-aside-badge" title="Good Health"><i class="fa-solid fa-heart-pulse"></i></div>
                    <div class="auth-aside-badge" title="Climate Action"><i class="fa-solid fa-earth-americas"></i></div>
                </div>
            </div>

            <div class="auth-form-panel">
                <h1>Create Account</h1>
                <p class="subtitle">Lengkapi data diri di bawah ini untuk membuat akun ASVIS baru.</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="auth-field">
                        <label for="name">Nama Lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkapmu" required autofocus autocomplete="name">
                        @error('name')
                            <div class="auth-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autocomplete="username">
                        @error('email')
                            <div class="auth-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label for="weight_kg">Berat Badan (kg) <small style="font-weight: normal; color: #888;">(Opsional)</small></label>
                            <input id="weight_kg" type="number" step="0.1" min="20" max="300" name="weight_kg" value="{{ old('weight_kg') }}" placeholder="Contoh: 65">
                            @error('weight_kg')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="height_cm">Tinggi Badan (cm) <small style="font-weight: normal; color: #888;">(Opsional)</small></label>
                            <input id="height_cm" type="number" step="0.1" min="50" max="250" name="height_cm" value="{{ old('height_cm') }}" placeholder="Contoh: 170">
                            @error('height_cm')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" placeholder="Min. 8 karakter" required autocomplete="new-password">
                            @error('password')
                                <div class="auth-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password" required autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="auth-submit-btn" style="margin-top: 8px;">Daftar Akun</button>
                </form>

                <div class="auth-switch">
                    Sudah punya akun? <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>