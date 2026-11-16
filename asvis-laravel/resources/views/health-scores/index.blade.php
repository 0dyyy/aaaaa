<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Link to the CSS file in the public directory -->
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
</head>
<body>

    <nav id="navbar">
        <a href="{{ url('/') }}" class="logo">ASVIS</a>
        <ul class="nav-links">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="#">Food Tracker</a></li>
            <li><a href="#">Exercise</a></li>
            <li><a href="{{ route('health-scores.index') }}">Summary</a></li>
            
            {{-- Link Admin (Hanya muncul jika yang login adalah admin) --}}
            @if(Auth::user()->isAdmin())
                <li><a href="{{ route('admin.dashboard') }}" style="color: #ff9900;">Admin Dashboard</a></li>
            @endif
        </ul>
        <div class="user-profile">
            <div class="user-info">
                {{-- Memanggil nama dan role langsung dari database --}}
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
            </div>
            
            {{-- Tombol Logout --}}
            <form method="POST" action="{{ route('logout') }}" style="margin-left: 15px;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #ff4d4d; cursor: pointer;" title="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </nav>

    <section class="summary-header">
        <h1>Your Health Index Score</h1>
        <div class="main-score">96.8</div>
        <p>Alimentation Saine, Vie Saine</p>
    </section>

    <main class="summary-content">
        <div class="top-row">
            <div class="score-card food-score">
                <h3>Food Score</h3>
                <div class="number">68.0</div>
            </div>
            <div class="score-card exercise-score">
                <h3>Exercise Score</h3>
                <div class="number">68.0</div>
            </div>
            <div class="suggestion-text">
                <h2>Sugestion</h2>
                <p>ASVIS adalah platform asisten kesehatan cerdas berbasis AI yang membantu pengguna melacak asupan nutrisi, aktivitas fisik, serta memberikan rekomendasi gaya hidup sehat yang terintegrasi secara real-time dengan data kualitas udara lingkungan.</p>
            </div>
        </div>

        <div class="bottom-row">
            <div class="large-suggestion">
                <h2>Sugestion</h2>
                <p>ASVIS adalah platform asisten kesehatan cerdas berbasis AI yang membantu pengguna melacak asupan nutrisi, aktivitas fisik, serta memberikan rekomendasi gaya hidup sehat yang terintegrasi secara real-time dengan data kualitas udara lingkungan.</p>
            </div>
            <div class="chart-container">
                <canvas id="nutritionChart"></canvas>
            </div>
        </div>

        <div class="bottom-blank-row">
            <div class="blank-white"></div>
            <div class="blank-green"></div>
        </div>
    </main>

    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 10) { nav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)'; } 
            else { nav.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'; }
        });

        const ctx = document.getElementById('nutritionChart').getContext('2d');
        const nutritionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Lemak', 'Protein', 'Serat', 'Karbohidrat', 'Natrium'],
                datasets: [{
                data: @json($macroData), 
                backgroundColor: ['#7bc000', '#69a800', '#578f00', '#447500', '#325c00'],
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
    </script>
</body>
</html>