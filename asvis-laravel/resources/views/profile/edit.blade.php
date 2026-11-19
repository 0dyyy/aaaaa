<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Profil Saya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
</head>
<body>

    <x-navbar />

    <section class="summary-header">
        <h1>Profile Settings</h1>
        <div class="main-score" style="font-size: 5rem; margin-top: 10px;">{{ $user->name }}</div>
        <p>Perbarui informasi akun, berat & tinggi badan, serta keamanan kata sandi.</p>
    </section>

    <main class="summary-content" style="max-width: 900px; margin: 0 auto; padding: 40px 20px;">

        @if (session('status') === 'profile-updated')
            <div class="auth-status">Profil dan data fisik berhasil diperbarui!</div>
        @elseif (session('status') === 'password-updated')
            <div class="auth-status">Password berhasil diperbarui!</div>
        @endif

        <!-- 1. Update Profile & Body Metrics -->
        <div style="background: #fff; border: 2px solid var(--border-light); border-radius: 20px; padding: 35px; margin-bottom: 30px; box-shadow: 0 6px 20px rgba(0,0,0,0.04);">
            <h2 style="font-family: var(--font-serif); font-size: 2rem; font-style: italic; color: var(--primary-brown); border-bottom: 2px solid var(--primary-brown); display: inline-block; margin-bottom: 10px;">
                Informasi Profil & Fisik
            </h2>
            <p style="color: #666; font-size: 0.95rem; margin-bottom: 25px;">
                Data berat dan tinggi badan digunakan untuk menghitung target kalori harian dan pembakaran kalori olahraga secara akurat.
            </p>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="auth-field">
                    <label for="name">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @error('email')
                        <div class="auth-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label for="weight_kg">Berat Badan (kg)</label>
                        <input id="weight_kg" name="weight_kg" type="number" step="0.1" min="20" max="300" value="{{ old('weight_kg', $user->weight_kg) }}" placeholder="Contoh: 65">
                        @error('weight_kg')
                            <div class="auth-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="height_cm">Tinggi Badan (cm)</label>
                        <input id="height_cm" name="height_cm" type="number" step="0.1" min="50" max="250" value="{{ old('height_cm', $user->height_cm) }}" placeholder="Contoh: 170">
                        @error('height_cm')
                            <div class="auth-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn" style="width: auto; padding: 12px 30px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </form>
        </div>

        <!-- 2. Update Password -->
        <div style="background: #fff; border: 2px solid var(--border-light); border-radius: 20px; padding: 35px; margin-bottom: 30px; box-shadow: 0 6px 20px rgba(0,0,0,0.04);">
            <h2 style="font-family: var(--font-serif); font-size: 2rem; font-style: italic; color: var(--primary-brown); border-bottom: 2px solid var(--primary-brown); display: inline-block; margin-bottom: 10px;">
                Ubah Password
            </h2>
            <p style="color: #666; font-size: 0.95rem; margin-bottom: 25px;">
                Gunakan kombinasi password yang kuat untuk menjaga keamanan akunmu.
            </p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="auth-field">
                    <label for="current_password">Password Saat Ini</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                    @if($errors->updatePassword->has('current_password'))
                        <div class="auth-error">{{ $errors->updatePassword->first('current_password') }}</div>
                    @endif
                </div>

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label for="password">Password Baru</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required>
                        @if($errors->updatePassword->has('password'))
                            <div class="auth-error">{{ $errors->updatePassword->first('password') }}</div>
                        @endif
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        @if($errors->updatePassword->has('password_confirmation'))
                            <div class="auth-error">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                        @endif
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn" style="width: auto; padding: 12px 30px; background-color: var(--dark-green);">
                    <i class="fa-solid fa-key"></i> Perbarui Password
                </button>
            </form>
        </div>

        <!-- 3. Delete Account -->
        <div style="background: #fff; border: 2px solid #ffcccc; border-radius: 20px; padding: 35px; box-shadow: 0 6px 20px rgba(0,0,0,0.04);">
            <h2 style="font-family: var(--font-serif); font-size: 2rem; font-style: italic; color: #cc0000; border-bottom: 2px solid #cc0000; display: inline-block; margin-bottom: 10px;">
                Hapus Akun
            </h2>
            <p style="color: #666; font-size: 0.95rem; margin-bottom: 20px;">
                Menghapus akun akan menghapus seluruh riwayat makanan, olahraga, dan skor kesehatan secara permanen. Masukkan password Anda untuk mengonfirmasi.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun secara permanen?');">
                @csrf
                @method('delete')

                <div class="auth-field" style="max-width: 400px;">
                    <label for="delete_password">Konfirmasi Password</label>
                    <input id="delete_password" name="password" type="password" placeholder="Masukkan password Anda" required>
                    @if($errors->userDeletion->has('password'))
                        <div class="auth-error">{{ $errors->userDeletion->first('password') }}</div>
                    @endif
                </div>

                <button type="submit" class="btn-delete-log" style="padding: 12px 25px; font-size: 1rem;">
                    <i class="fa-solid fa-trash"></i> Hapus Akun Permanen
                </button>
            </form>
        </div>

    </main>

</body>
</html>