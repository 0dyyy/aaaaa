<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
</head>
<body>

    <x-navbar />

    <section class="summary-header">
        <h1>Admin Dashboard</h1>
        <div class="main-score" style="font-size: 6rem;">{{ $stats['total_users'] }} Users</div>
        <p>Ringkasan aktivitas platform & manajemen pengguna ASVÍS</p>
    </section>

    <main class="summary-content">
        @if(session('success'))
            <div style="background-color: var(--light-green); color: white; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; font-weight: bold;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div style="background-color: #ffb347; color: var(--primary-brown); padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; font-weight: bold;">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Statistik Platform -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin: 30px 0;">
            <div class="score-card food-score">
                <h3>Total Pengguna</h3>
                <div class="number">{{ $stats['total_users'] }}</div>
            </div>
            <div class="score-card exercise-score">
                <h3>Total Log Makanan</h3>
                <div class="number">{{ $stats['total_food_logs'] }}</div>
            </div>
            <div class="score-card food-score" style="background-color: var(--primary-brown);">
                <h3>Total Log Olahraga</h3>
                <div class="number">{{ $stats['total_exercise_logs'] }}</div>
            </div>
            <div class="score-card exercise-score">
                <h3>Rata-rata Skor Kesehatan</h3>
                <div class="number">{{ $stats['avg_health_score'] ?: '–' }}</div>
            </div>
        </div>

        <!-- Tabel Manajemen Pengguna -->
        <div class="suggestion-text" style="padding: 0; margin-top: 40px; margin-bottom: 15px;">
            <h2>Daftar Pengguna</h2>
        </div>

        <div style="background-color: white; border-radius: 15px; overflow-x: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px;">
            <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                <thead style="background-color: var(--primary-brown); color: white; text-align: left;">
                    <tr>
                        <th style="padding: 15px;">Nama</th>
                        <th style="padding: 15px;">Email</th>
                        <th style="padding: 15px;">Fisik (BB / TB)</th>
                        <th style="padding: 15px;">Aktivitas Log</th>
                        <th style="padding: 15px;">Peran</th>
                        <th style="padding: 15px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;"><strong>{{ $u->name }}</strong></td>
                            <td style="padding: 15px;">{{ $u->email }}</td>
                            <td style="padding: 15px;">
                                {{ $u->weight_kg ? $u->weight_kg . ' kg' : '–' }} /
                                {{ $u->height_cm ? $u->height_cm . ' cm' : '–' }}
                            </td>
                            <td style="padding: 15px;">
                                {{ $u->food_logs_count }} makanan, {{ $u->exercise_logs_count }} olahraga
                            </td>
                            <td style="padding: 15px;">
                                <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; background-color: {{ $u->isAdmin() ? '#ffb347' : '#e0f2f1' }}; color: #333;">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td style="padding: 15px; display: flex; gap: 10px;">
                                @if($u->id !== Auth::id())
                                    <form method="POST" action="{{ route('admin.users.toggle-role', $u) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" style="background-color: var(--dark-green); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                                            Jadikan {{ $u->isAdmin() ? 'User' : 'Admin' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus pengguna {{ $u->name }} beserta seluruh datanya?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-log" style="padding: 6px 12px; font-size: 0.85rem;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <small style="color: #888;">Akun Anda</small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Aktivitas Makanan Terbaru -->
        <div class="suggestion-text" style="padding: 0; margin-bottom: 15px;">
            <h2>Log Makanan Terbaru</h2>
        </div>

        <div style="background-color: white; border-radius: 15px; overflow-x: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-green); color: white; text-align: left;">
                    <tr>
                        <th style="padding: 15px;">Waktu</th>
                        <th style="padding: 15px;">Pengguna</th>
                        <th style="padding: 15px;">Makanan</th>
                        <th style="padding: 15px;">Kalori</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentFoodLogs as $log)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">{{ $log->consumed_at?->format('d M Y, H:i') }}</td>
                            <td style="padding: 15px;">{{ $log->user?->name ?? '–' }}</td>
                            <td style="padding: 15px;"><strong>{{ $log->food_name }}</strong></td>
                            <td style="padding: 15px;">{{ $log->calories }} kkal</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 25px; text-align: center; color: #888;">Belum ada aktivitas log makanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>