<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
                {{-- Jika user sudah login --}}
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
                {{-- Jika user belum login (Guest) --}}
                <div class="user-info">
                    <a href="{{ route('login') }}" style="margin-right: 15px; text-decoration: none; color: #333;">Login</a>
                    <a href="{{ route('register') }}" style="text-decoration: none; color: #333; font-weight: bold;">Register</a>
                </div>
            @endauth
        </div>
    </nav>

    <section class="hero">
        <h2>Alimentation Saine, Vie Saine</h2>
        <div class="giant-text">ASVIS</div>
    </section>

    <main>
        <section class="sdg-section">
            <div class="sdg-left">
                <h3 class="section-title">Our SDG</h3>
                <div class="sdg-icons">
                    <div class="sdg-box sdg-2">
                        <span>2. ZERO HUNGER</span>
                        <i class="fa-solid fa-bowl-food" style="margin-top: 5px;"></i>
                    </div>
                    <div class="sdg-box sdg-3">
                        <span>3. GOOD HEALTH</span>
                        <i class="fa-solid fa-heart-pulse" style="margin-top: 5px;"></i>
                    </div>
                    <div class="sdg-box sdg-13">
                        <span>13. CLIMATE ACTION</span>
                        <i class="fa-solid fa-earth-americas" style="margin-top: 5px;"></i>
                    </div>
                </div>
            </div>
            <div class="sdg-right">
                <h3 class="section-title" style="font-style: italic; border-bottom-width: 1px;">ASVIS</h3>
                <p>ASVIS adalah platform asisten kesehatan cerdas berbasis AI yang membantu pengguna melacak asupan nutrisi, aktivitas fisik, serta memberikan rekomendasi gaya hidup sehat yang terintegrasi secara real-time dengan data kualitas udara lingkungan.</p>
            </div>
        </section>

        <section class="features-grid">
            <div class="feature-dark-card">
                <div>
                    <h4>ASVIS</h4>
                    <h3 class="section-title">FEATURE</h3>
                </div>
                <div class="feature-item">
                    <p>Mencatat makanan, melacak kalori, dan memantau nutrisi dengan bantuan AI.</p>
                    <div class="icon-box"><i class="fa-solid fa-leaf"></i></div>
                </div>
                <div class="feature-item">
                    <div class="icon-box"><i class="fa-solid fa-person-biking"></i></div>
                    <p>Melacak aktivitas olahraga dan memberi rekomendasi waktu serta lokasi berdasarkan cuaca dan AQI.</p>
                </div>
                <div class="feature-item">
                    <p>Memberikan skor kesehatan dan insight AI berdasarkan pola makan dan aktivitas olahraga.</p>
                    <div class="icon-box"><i class="fa-solid fa-circle-exclamation"></i></div>
                </div>
            </div>

            <div class="feature-light-grid">
                <a href="{{ route('food-logs.index') }}" class="light-card" title="Food Tracker" aria-label="Food Tracker">
                    <i class="fa-solid fa-leaf"></i>
                </a>
                <a href="{{ route('exercise-logs.index') }}" class="light-card" title="Exercise" aria-label="Exercise">
                    <i class="fa-solid fa-person-biking"></i>
                </a>
                <a href="{{ route('health-scores.index') }}" class="light-card light-card-wide" title="Summary" aria-label="Summary">
                    <i class="fa-regular fa-comment-dots"></i>
                </a>
            </div>
        </section>
    </main>

    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 10) { nav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)'; } 
            else { nav.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'; }
        });
    </script>
</body>
</html>