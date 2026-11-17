<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Summary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Link to the CSS file in the public directory -->
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
</head>
<body>

    <nav id="navbar">
        <a href="{{ url('/') }}" class="logo">ASVIS</a>
        <ul class="nav-links">
            <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('food-logs.index') }}" class="{{ request()->routeIs('food-logs.index') ? 'active' : '' }}">Food Tracker</a></li>
            <li><a href="#">Exercise</a></li>
            <li><a href="{{ route('health-scores.index') }}" class="{{ request()->routeIs('health-scores.index') ? 'active' : '' }}">Summary</a></li>

            @auth
                @if(Auth::user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" style="color: #ff9900;">Admin Dashboard</a></li>
                @endif
            @endauth
        </ul>
        <div class="user-profile">
            @auth
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>

                <form method="POST" action="{{ route('logout') }}" style="margin-left: 15px;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: #ff4d4d; cursor: pointer;" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            @else
                <div class="user-info">
                    <a href="{{ route('login') }}" style="margin-right: 15px; text-decoration: none; color: #333;">Login</a>
                    <a href="{{ route('register') }}" style="text-decoration: none; color: #333; font-weight: bold;">Register</a>
                </div>
            @endauth
        </div>
    </nav>

    @php
        $hasMacros = array_sum($summary['macros']) > 0;
    @endphp

    <section class="summary-header">
        <h1>Your Health Index Score</h1>
        <div class="main-score">{{ $summary['total_score'] ?? '–' }}</div>
        <p>Alimentation Saine, Vie Saine</p>
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
        @if($errors->any())
            <div style="background-color: #ff4d4d; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="top-row">
            <div class="score-card food-score">
                <h3>Food Score</h3>
                <div class="number">{{ $summary['nutrition_score'] ?? '–' }}</div>
            </div>
            <div class="score-card exercise-score">
                <h3>Exercise Score</h3>
                <div class="number">{{ $summary['activity_score'] ?? '–' }}</div>
                @if($summary['activity_score'] === null)
                    <small style="margin-top: 8px;">Belum ada data olahraga</small>
                @endif
            </div>
            <div class="suggestion-text">
                <h2>Suggestion</h2>
                <p>{{ $summary['tip'] }}</p>
            </div>
        </div>

        <div class="bottom-row">
            <div class="large-suggestion">
                <h2>AI Insight</h2>
                <p>
                    {{ $summary['insight'] ?? ($summary['has_food']
                        ? 'Belum ada insight untuk hari ini. Tekan tombol di bawah untuk membuat insight AI dari data makananmu.'
                        : 'Catat makananmu dulu di Food Tracker, lalu insight AI bisa dibuat di sini.') }}
                </p>

                @if($summary['has_food'])
                    <form method="POST" action="{{ route('health-scores.generate') }}" style="margin-top: 25px;">
                        @csrf
                        <button type="submit" style="background-color: var(--primary-brown); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-family: var(--font-sans); font-weight: bold; font-size: 1rem; cursor: pointer;">
                            {{ $summary['insight'] ? 'Perbarui Insight AI' : 'Buat Insight AI' }}
                        </button>
                    </form>
                    @if($summary['insight'])
                        <small style="margin-top: 10px; opacity: 0.85;">Sudah menambah makanan baru? Tekan perbarui agar insight mengikuti data terbaru.</small>
                    @endif
                @endif
            </div>

            <div class="chart-container">
                @if($hasMacros)
                    <canvas id="nutritionChart"></canvas>
                @else
                    <p style="color: #888; text-align: center;">Belum ada data makronutrien hari ini.</p>
                @endif
            </div>
        </div>

        <div class="bottom-blank-row">
            <div class="blank-white" style="padding: 25px; overflow-y: auto;">
                <h3 style="font-family: var(--font-serif); font-size: 1.6rem; color: var(--primary-brown); margin-bottom: 15px;">Riwayat Skor</h3>
                @forelse($history as $row)
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                        <span>{{ \Carbon\Carbon::parse($row->score_date)->format('d M Y') }}</span>
                        <strong>{{ $row->total_score }}</strong>
                    </div>
                @empty
                    <p style="color: #888;">Belum ada riwayat skor.</p>
                @endforelse
            </div>

            <div class="blank-green" style="padding: 25px; color: white;">
                <h3 style="font-family: var(--font-serif); font-size: 1.6rem; margin-bottom: 25px;">Ringkasan Kalori Hari Ini</h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; text-align: center;">
                    <div>
                        <div style="font-family: var(--font-serif); font-size: 2.5rem; font-weight: 700;">{{ number_format($summary['calories_in']) }}</div>
                        <small>Kalori masuk (kkal)</small>
                    </div>
                    <div>
                        <div style="font-family: var(--font-serif); font-size: 2.5rem; font-weight: 700;">{{ number_format($summary['calories_target']) }}</div>
                        <small>Target harian (kkal)</small>
                    </div>
                    <div>
                        <div style="font-family: var(--font-serif); font-size: 2.5rem; font-weight: 700;">{{ $summary['calories_burned'] ? number_format($summary['calories_burned']) : '–' }}</div>
                        <small>Kalori terbakar (kkal)</small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 10) { nav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)'; }
            else { nav.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'; }
        });

        @if($hasMacros)
        const ctx = document.getElementById('nutritionChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Lemak', 'Protein', 'Karbohidrat'],
                datasets: [{
                    data: @json(array_values($summary['macros'])),
                    backgroundColor: ['#7bc000', '#578f00', '#325c00'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 20, font: { size: 16 } }
                    }
                }
            }
        });
        @endif
    </script>
</body>
</html>