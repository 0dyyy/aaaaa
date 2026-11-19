<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Exercise Tracker</title>
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        /* ===== Khusus halaman Exercise (sisanya pakai style.css) ===== */
        .ex-section { max-width: 1100px; margin: 0 auto 50px; padding: 0 20px; }
        .ex-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .ex-title { font-family: var(--font-serif); font-size: 1.8rem; color: var(--primary-brown); margin-bottom: 5px; }
        .ex-subtitle { color: #777; font-size: 0.95rem; margin-bottom: 20px; }

        /* Prediksi AQI per jam */
        .aqi-forecast { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .aqi-chart-box { position: relative; height: 260px; }
        .best-time-card { background: #eef2e3; border-radius: 15px; padding: 25px; display: flex; flex-direction: column; gap: 6px; }
        .best-time-card .label { font-size: 0.8rem; font-weight: 700; color: var(--accent-green); text-transform: uppercase; letter-spacing: 1px; }
        .best-time-card .time { font-family: var(--font-serif); font-size: 2.2rem; color: var(--primary-brown); }
        .best-time-card .range { color: var(--accent-green); font-weight: 700; }
        .best-time-card hr { border: none; border-top: 1px solid #d5dcc4; margin: 10px 0; }
        .best-time-card .now-aqi { font-family: var(--font-serif); font-size: 2rem; color: var(--primary-brown); }

        /* Peta + daftar lokasi */
        .nearby { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        #map { height: 380px; border-radius: 15px; position: relative; z-index: 0; }
        .place-list { display: flex; flex-direction: column; gap: 15px; max-height: 380px; overflow-y: auto; }
        .place-item { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 18px; border: 1px solid var(--border-light); border-radius: 15px; background: #fff; cursor: pointer; transition: border-color .2s; }
        .place-item:hover, .place-item.best { border-color: var(--accent-green); }
        .place-item.best { background: #f3f6ea; }
        .place-name { font-weight: 700; color: var(--primary-brown); }
        .place-meta { font-size: 0.85rem; color: #777; margin-top: 4px; }
        .aqi-badge { min-width: 64px; text-align: center; padding: 8px; border-radius: 10px; font-family: var(--font-serif); font-size: 1.5rem; }
        .aqi-badge small { display: block; font-family: var(--font-sans); font-size: 0.7rem; }
        .aqi-good { background: #dfe9c8; color: var(--accent-green-dark); }
        .aqi-moderate { background: #f6e3b4; color: #8a6a00; }
        .aqi-bad { background: #f5d0c5; color: #a33a1d; }
        .aqi-none { background: #eee; color: #777; }
        .legend { display: flex; gap: 15px; flex-wrap: wrap; font-size: 0.8rem; color: #777; margin-top: 12px; }
        .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }

        /* Sesi terakhir */
        .last-session { background: #e9e4da; border-radius: 20px; padding: 30px; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr 1.5fr; gap: 20px; align-items: center; }
        .stat-label { font-size: 0.8rem; color: #777; }
        .stat-value { font-family: var(--font-serif); font-size: 2.2rem; color: var(--primary-brown); }

        /* Tombol & form */
        .btn-green { background: var(--accent-green); color: #fff; border: none; padding: 14px 20px; border-radius: 10px; font-family: var(--font-sans); font-weight: 700; font-size: 1rem; cursor: pointer; width: 100%; }
        .btn-green:hover { background: var(--accent-green-dark); }
        .btn-small { padding: 6px 12px; font-size: 0.8rem; width: auto; margin-top: 8px; }

        /* ===== Add Entries (layar 1–4) ===== */
        #overlay-add { overflow-y: auto; align-items: flex-start; padding: 110px 0 40px; }
        #overlay-fullmap { z-index: 1500; }
        .entry-title { font-family: var(--font-serif); font-size: 3rem; color: var(--primary-brown); text-align: center; margin-bottom: 30px; }
        .entry-title span { border-bottom: 2px solid var(--primary-brown); }
        .map-frame { border: 10px solid var(--accent-green); border-radius: 20px; overflow: hidden; position: relative; background: #fff; }
        .map-frame.tall { height: 480px; }
        .map-frame.wide { height: 320px; margin-bottom: 25px; }
        .leaflet-map { width: 100%; height: 100%; position: relative; z-index: 0; }
        .btn-expand { position: absolute; top: 10px; right: 10px; z-index: 500; background: #fff; border: 2px solid var(--primary-brown); border-radius: 8px; width: 38px; height: 38px; cursor: pointer; color: var(--primary-brown); }
        .ai-overview-card.show, .action-icon-buttons.show { display: block; }
        .action-icon-buttons.show { display: flex; }
        .card-sub { text-align: center; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem; color: #ddd; margin: -10px 0 25px; }
        .step2-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; align-items: start; }
        .stats-box { background: var(--primary-brown); color: #fff; border-radius: 15px; padding: 30px; min-height: 280px; }
        .stats-box h3 { font-family: var(--font-serif); font-style: italic; font-size: 2.5rem; text-align: center; margin-bottom: 15px; }
        .stats-box ul { list-style: none; line-height: 2; }
        .stats-box label { display: block; font-size: 0.85rem; color: #ddd; margin-bottom: 6px; }
        .stats-box input { width: 100%; padding: 10px; border: none; border-radius: 8px; font-family: var(--font-sans); font-size: 1rem; margin-bottom: 15px; }
        .stats-box input[readonly] { background: #e9e4da; color: #555; }
        .tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .tile { border-radius: 12px; padding: 18px; color: #fff; text-align: center; background: var(--accent-green); }
        .tile.light { background: var(--light-green); }
        .tile label { display: block; font-size: 1rem; margin-bottom: 8px; }
        .tile .big { font-family: var(--font-serif); font-size: 2rem; }
        .tile select, .tile input { width: 100%; padding: 8px; border-radius: 8px; border: none; font-family: var(--font-sans); font-size: 1rem; }
        .btn-close-map { position: absolute; top: 15px; right: 15px; z-index: 500; background: #fff; border: 3px solid var(--primary-brown); border-radius: 50%; width: 50px; height: 50px; font-size: 1.4rem; cursor: pointer; color: var(--primary-brown); }
        .added-overlay { position: fixed; inset: 0; z-index: 2000; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(8px); background: rgba(244, 241, 234, 0.6); transition: opacity .5s; cursor: pointer; }
        .added-overlay h2 { font-size: 6rem; font-weight: 700; color: var(--light-green); line-height: 1; }
        .added-overlay p { font-size: 2.5rem; font-weight: 700; color: var(--light-green); }
        .added-overlay.hide { opacity: 0; pointer-events: none; }

        @media (max-width: 900px) {
            .aqi-forecast, .nearby, .add-entry-layout, .step2-grid { grid-template-columns: 1fr; }
            .last-session { grid-template-columns: 1fr 1fr; }
            .added-overlay h2 { font-size: 3.5rem; }
        }
    </style>
</head>
<body>

    {{-- NAVBAR (sama seperti Food Tracker) --}}
    <nav id="navbar">
        <a href="{{ url('/') }}" class="logo">ASVIS</a>
        <ul class="nav-links">
            <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('food-logs.index') }}" class="{{ request()->routeIs('food-logs.index') ? 'active' : '' }}">Food Tracker</a></li>
            <li><a href="{{ route('exercise-logs.index') }}" class="{{ request()->routeIs('exercise-logs.index') ? 'active' : '' }}">Exercise</a></li>
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
            @endauth
        </div>
    </nav>

    @php
        // Kelompokkan log per tanggal (untuk kalender)
        $logsByDate = $exerciseLogs->groupBy(fn ($log) => $log->exercised_at->format('Y-m-d'));
        $todayCount = $logsByDate->get(now()->toDateString(), collect())->count();

        // Data log untuk JavaScript (popup detail per tanggal)
        $logsForJs = $logsByDate->map(fn ($logs) => $logs->map(fn ($log) => [
            'id' => $log->id,
            'type' => $log->exercise_type,
            'time' => $log->exercised_at->format('H:i'),
            'duration' => $log->duration_minutes,
            'calories' => $log->calories_burned,
            'location' => $log->location->name ?? '-',
            'air' => $log->air_quality_during_exercise ?? '-',
        ])->values());

        // Lokasi tersimpan untuk peta
        $locationsForJs = $locations->filter(fn ($l) => $l->latitude && $l->longitude)->map(fn ($l) => [
            'id' => $l->id,
            'name' => $l->name,
            'lat' => (float) $l->latitude,
            'lng' => (float) $l->longitude,
        ])->values();

        // Sesi terakhir (controller sudah urut dari terbaru)
        $lastSession = $exerciseLogs->first();
        $lastAqi = null;
        $lastAqiLabel = '-';
        if ($lastSession && preg_match('/^(.*)\(AQI:\s*(\d+)\)/', $lastSession->air_quality_during_exercise ?? '', $m)) {
            $lastAqiLabel = trim($m[1]);
            $lastAqi = $m[2];
        }
    @endphp

    {{-- HERO --}}
    <section class="tracker-hero" style="justify-content: center;">
        <div class="hero-center">
            <h1>Hi <em>{{ Auth::user()->name }}</em></h1>
            <p>Siap bergerak dengan udara yang lebih baik? Hari ini kamu mencatat {{ $todayCount }} olahraga.</p>
            <div class="action-buttons">
                <div class="action-btn-group">
                    <p>Calender</p>
                    <button class="btn-box" id="btn-open-calendar"><i class="fa-regular fa-calendar"></i></button>
                </div>
                <div class="action-btn-group">
                    <p>Add Entries</p>
                    <button class="btn-box" id="btn-open-add"><i class="fa-solid fa-comment-dots"></i></button>
                </div>
            </div>
        </div>
    </section>

    {{-- PESAN ERROR (pesan sukses ditampilkan sebagai layar "ADDED to Entries!") --}}
    @if($errors->any())
        <div style="background:#ff4d4d;color:white;padding:15px;text-align:center;">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <h2 class="page-title">EXERCISE TRACKER</h2>

    {{-- KALENDER MINGGU INI (sama seperti Food Tracker) --}}
    @php $startOfWeek = now()->startOfWeek(); @endphp
    <div class="calendar-strip">
        @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
            <div class="cal-day-header">{{ $d }}</div>
        @endforeach
        @for($i = 0; $i < 7; $i++)
            @php
                $date = $startOfWeek->copy()->addDays($i);
                $dateString = $date->format('Y-m-d');
                $hasLogs = $logsByDate->has($dateString);
            @endphp
            <div class="cal-date {{ $hasLogs ? 'active-day-start cal-day-clickable' : '' }}" @if($hasLogs) data-date="{{ $dateString }}" style="cursor:pointer;" @endif>
                {{ $date->format('d') }}
            </div>
        @endfor
    </div>

    {{-- 1. PREDIKSI AQI PER JAM --}}
    <section class="ex-section">
        <div class="ex-card aqi-forecast">
            <div>
                <h2 class="ex-title">Udara lebih baik, olahraga lebih nyaman.</h2>
                <p class="ex-subtitle">Prediksi AQI per jam · <span id="forecast-place">memuat lokasi...</span></p>
                <div class="aqi-chart-box"><canvas id="aqiChart"></canvas></div>
            </div>
            <div class="best-time-card">
                <span class="label"><i class="fa-solid fa-leaf"></i> Waktu terbaik</span>
                <span class="time" id="best-time">--.--</span>
                <span class="range" id="best-range">Memuat prediksi...</span>
                <p style="font-size: 0.85rem; color: #555;">Rentang dengan prediksi AQI terendah untuk aktivitas outdoor hari ini.</p>
                <hr>
                <span class="stat-label">AQI lokasi kamu saat ini</span>
                <span class="now-aqi" id="now-aqi">--</span>
                <span class="stat-label" id="now-aqi-label">skala US AQI</span>
            </div>
        </div>
    </section>

    {{-- 2. PILIH UDARA YANG LEBIH BAIK --}}
    <section class="ex-section">
        <h2 class="ex-title">Pilih udara yang lebih baik di sekitarmu.</h2>
        <p class="ex-subtitle">Bandingkan AQI dan jarak sebelum menentukan lokasi olahraga.</p>
        <div class="nearby">
            <div>
                <div id="map"></div>
                <div class="legend">
                    <span><span class="dot" style="background:#82a380;"></span>Baik 0–50</span>
                    <span><span class="dot" style="background:#e0b84a;"></span>Sedang 51–100</span>
                    <span><span class="dot" style="background:#d9694a;"></span>Tidak sehat &gt;100</span>
                </div>
            </div>
            <div>
                <div class="place-list" id="place-list"><p class="place-meta">Memuat lokasi...</p></div>
                <p class="legend">AQI perkiraan Open-Meteo · skala US AQI · <a href="{{ route('locations.index') }}">Kelola lokasi</a></p>
            </div>
        </div>
    </section>

    {{-- 3. SESI TERAKHIR --}}
    <section class="ex-section">
        <div class="last-session">
            @if($lastSession)
                <div>
                    <h3 class="ex-title">Sesi terakhir</h3>
                    <div class="place-name">{{ $lastSession->exercise_type }}</div>
                    <div class="place-meta">{{ $lastSession->exercised_at->format('d M Y · H:i') }} · {{ $lastSession->location->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="stat-label">Durasi</div>
                    <div class="stat-value">{{ $lastSession->duration_minutes }}</div>
                    <div class="stat-label">menit</div>
                </div>
                <div>
                    <div class="stat-label">Kalori terbakar</div>
                    <div class="stat-value">{{ $lastSession->calories_burned }}</div>
                    <div class="stat-label">kkal</div>
                </div>
                <div>
                    <div class="stat-label">AQI saat sesi</div>
                    <div class="stat-value">{{ $lastAqi ?? '-' }}</div>
                    <div class="stat-label">{{ $lastAqiLabel }}</div>
                </div>
            @else
                <div style="grid-column: span 4;">
                    <h3 class="ex-title">Sesi terakhir</h3>
                    <p class="place-meta">Belum ada olahraga yang dicatat. Mulai sesi pertamamu!</p>
                </div>
            @endif
            <div>
                <button type="button" class="btn-green" id="btn-start-session">Mulai Sesi Baru</button>
                <p class="place-meta" id="selected-place-text">Pilih lokasi dari daftar di atas atau di form.</p>
            </div>
        </div>
    </section>

    {{-- OVERLAY: KALENDER BULAN INI --}}
    <div class="overlay" id="overlay-calendar">
        <div class="modal-content">
            <div class="modal-nav">
                <div class="modal-month">{{ now()->format('F Y') }}</div>
                <div class="close-modal" id="close-calendar">Tutup kalender</div>
            </div>
            <div class="full-calendar-grid">
                @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
                    <div class="full-cal-header">{{ $d }}</div>
                @endforeach
                @php
                    $firstDayOfMonth = now()->startOfMonth();
                    $startOffset = $firstDayOfMonth->dayOfWeekIso - 1;
                @endphp
                @for($i = 0; $i < $startOffset; $i++)
                    <div class="full-cal-cell empty-month"></div>
                @endfor
                @for($day = 1; $day <= now()->daysInMonth; $day++)
                    @php
                        $dateString = $firstDayOfMonth->copy()->addDays($day - 1)->format('Y-m-d');
                        $hasLogs = $logsByDate->has($dateString);
                    @endphp
                    <div class="full-cal-cell {{ $hasLogs ? 'active-start cal-day-clickable' : '' }} {{ $dateString === now()->toDateString() ? 'current-day' : '' }}" @if($hasLogs) data-date="{{ $dateString }}" @endif>
                        {{ $day }}
                    </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- OVERLAY: DETAIL LOG PER TANGGAL --}}
    <div class="overlay" id="overlay-details">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-nav">
                <div class="modal-month" id="details-date-title" style="font-family: var(--font-serif); font-size: 2rem; color: var(--primary-brown);"></div>
                <div class="close-modal" id="close-details"><i class="fa-solid fa-xmark"></i> Tutup</div>
            </div>
            <div class="log-details-list" id="log-details-list"></div>
        </div>
    </div>

    {{-- OVERLAY: ADD ENTRIES (layar 1 & 2) --}}
    <div class="overlay" id="overlay-add">
        <div class="modal-content">
            <div class="modal-nav">
                <div></div>
                <div class="close-modal" id="close-add">Tutup</div>
            </div>

            <form method="POST" action="{{ route('exercise-logs.store') }}" id="entry-form">
                @csrf
                <input type="hidden" name="location_id" id="input-location" value="{{ old('location_id') }}">
                <input type="hidden" name="latitude" id="input-lat" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="input-lng" value="{{ old('longitude') }}">
                <input type="hidden" name="exercised_at" id="input-time" value="{{ old('exercised_at', now()->format('Y-m-d\TH:i')) }}">

                {{-- LAYAR 1: pilih lokasi + AI Overview --}}
                <div id="step-1">
                    <h2 class="entry-title"><span>Add Entries</span></h2>
                    <div class="add-entry-layout">
                        <div class="map-frame tall">
                            <div id="pick-map" class="leaflet-map"></div>
                            <button type="button" class="btn-expand" data-expand title="Perbesar peta"><i class="fa-solid fa-expand"></i></button>
                        </div>
                        <div class="info-section">
                            <div class="ai-overview-card show">
                                <h3>AI Overview</h3>
                                <p class="card-sub" id="ai-sub">Pilih lokasi di peta</p>
                                <p id="ai-text">Klik penanda lokasi tersimpan atau titik mana saja di peta untuk memilih tempat olahraga.</p>
                            </div>
                            <div class="action-icon-buttons show">
                                <button type="button" class="icon-btn" id="btn-next" title="Lanjut"><i class="fa-solid fa-plus"></i></button>
                                <button type="button" class="icon-btn" id="btn-reset" title="Pilih ulang"><i class="fa-solid fa-rotate-left"></i></button>
                            </div>
                            @if($locations->isEmpty())
                                <p class="place-meta" style="margin-top: 15px;">Belum ada lokasi. <a href="{{ route('locations.index') }}">Tambah lokasi dulu</a>.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- LAYAR 2: detail + jenis, cuaca, AQI, kalori --}}
                <div id="step-2" style="display: none;">
                    <div class="map-frame wide">
                        <div id="detail-map" class="leaflet-map"></div>
                        <button type="button" class="btn-expand" data-expand title="Perbesar peta"><i class="fa-solid fa-expand"></i></button>
                    </div>
                    <div class="step2-grid">
                        <div class="stats-box">
                            <h3>Stats</h3>
                            <p class="card-sub">Detail lokasi</p>
                            <label for="location_name">Nama tempat</label>
                            <input type="text" id="location_name" name="location_name" value="{{ old('location_name') }}" maxlength="255" placeholder="Contoh: Lapangan dekat kos">
                            <ul id="stats-list"></ul>
                        </div>
                        <div>
                            <div class="tiles">
                                <div class="tile">
                                    <label for="exercise_type">Type of Exercise</label>
                                    <select name="exercise_type" id="exercise_type" required>
                                        @foreach(['Lari', 'Jalan Kaki', 'Bersepeda', 'Renang', 'Sepak Bola', 'Futsal', 'Badminton', 'Senam', 'Yoga'] as $type)
                                            <option value="{{ $type }}" @selected(old('exercise_type') === $type)>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="tile light">
                                    <label>Cuaca</label>
                                    <div class="big" id="tile-weather" style="font-size: 1.4rem;">-</div>
                                </div>
                                <div class="tile light">
                                    <label>AQI Index</label>
                                    <div class="big" id="tile-aqi">-</div>
                                    <small id="tile-aqi-label"></small>
                                </div>
                                <div class="tile">
                                    <label for="duration_minutes">Durasi (menit)</label>
                                    <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1" placeholder="30" required>
                                </div>
                                <div class="tile" style="grid-column: span 2;">
                                    <label>Estimated Calories</label>
                                    <div class="big" id="tile-calories">-</div>
                                </div>
                            </div>
                            <div class="action-icon-buttons show">
                                <button type="submit" class="icon-btn" title="Simpan"><i class="fa-solid fa-check"></i></button>
                                <button type="button" class="icon-btn" id="btn-back" title="Kembali"><i class="fa-solid fa-rotate-left"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- OVERLAY: PETA LAYAR PENUH (layar 4) --}}
    <div class="overlay" id="overlay-fullmap">
        <div class="map-frame" style="width: 95vw; height: 85vh;">
            <div id="full-map" class="leaflet-map"></div>
            <button type="button" class="btn-close-map" id="close-fullmap" title="Tutup"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    {{-- LAYAR 3: ADDED to Entries! (muncul setelah berhasil simpan) --}}
    @if(session('success'))
        <div class="added-overlay" id="added-overlay">
            <h2>ADDED</h2>
            <p>to Entries!</p>
        </div>
    @endif

    {{-- Data dari Laravel ke JavaScript --}}
    <script>
        const exerciseLogsByDate = @json($logsForJs);
        const savedLocations = @json($locationsForJs);
        const csrfToken = @json(csrf_token());
        const reopenForm = @json($errors->any());
        const oldLocationId = @json(old('location_id'));
        const oldPoint = @json(old('latitude') ? ['lat' => (float) old('latitude'), 'lng' => (float) old('longitude'), 'name' => old('location_name')] : null);
        const userWeight = @json(Auth::user()->weight_kg ?? 65);
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ===== Helper =====
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        // Kategori sama dengan ExerciseLogController
        function aqiInfo(aqi) {
            if (aqi === null || aqi === undefined) return { label: 'N/A', cls: 'aqi-none' };
            if (aqi <= 50) return { label: 'Baik', cls: 'aqi-good' };
            if (aqi <= 100) return { label: 'Sedang', cls: 'aqi-moderate' };
            if (aqi <= 150) return { label: 'Tidak Sehat (Sensitif)', cls: 'aqi-bad' };
            if (aqi <= 200) return { label: 'Tidak Sehat', cls: 'aqi-bad' };
            return { label: 'Berbahaya', cls: 'aqi-bad' };
        }

        // Jarak dua koordinat dalam km (rumus Haversine)
        function distanceKm(lat1, lng1, lat2, lng2) {
            const toRad = (d) => d * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
            return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        // ===== Modal (sama seperti Food Tracker) =====
        const overlayCalendar = document.getElementById('overlay-calendar');
        const overlayAdd = document.getElementById('overlay-add');
        const overlayDetails = document.getElementById('overlay-details');

        document.getElementById('btn-open-calendar').addEventListener('click', () => overlayCalendar.classList.add('active'));
        document.getElementById('close-calendar').addEventListener('click', () => overlayCalendar.classList.remove('active'));
        document.getElementById('close-details').addEventListener('click', () => overlayDetails.classList.remove('active'));

        // Klik tanggal hijau -> tampilkan log hari itu
        document.querySelectorAll('.cal-day-clickable').forEach(cell => {
            cell.addEventListener('click', function () {
                const dateStr = this.dataset.date;
                const logs = exerciseLogsByDate[dateStr];
                if (!logs) return;

                document.getElementById('details-date-title').innerText =
                    new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

                document.getElementById('log-details-list').innerHTML = logs.map(log => `
                    <div class="log-card">
                        <div class="log-card-header">
                            <div class="log-card-title">${escapeHtml(log.type)}</div>
                            <div class="log-card-time"><i class="fa-regular fa-clock"></i> ${log.time}</div>
                        </div>
                        <div class="log-card-body">
                            <div class="log-card-info">
                                <p><strong><i class="fa-solid fa-fire"></i> ${log.calories} kkal</strong> · ${log.duration} menit</p>
                                <p><i class="fa-solid fa-location-dot"></i> ${escapeHtml(log.location)}</p>
                                <p><i class="fa-solid fa-wind"></i> ${escapeHtml(log.air)}</p>
                            </div>
                            <div>
                                <form action="/tracker-olahraga/${log.id}" method="POST" onsubmit="return confirm('Hapus log olahraga ini?');">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn-delete-log"><i class="fa-solid fa-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                `).join('');

                overlayCalendar.classList.remove('active');
                overlayDetails.classList.add('active');
            });
        });

        // ===== Peta Leaflet =====
        const DEFAULT_POS = [-6.2, 106.816]; // Jakarta
        const map = L.map('map').setView(DEFAULT_POS, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const markers = {};
        savedLocations.forEach(loc => {
            markers[loc.id] = L.marker([loc.lat, loc.lng]).addTo(map).bindPopup(`<b>${escapeHtml(loc.name)}</b>`);
        });

        // Minta lokasi pengguna dari GPS browser
        map.locate({ setView: false, maximumAge: 600000 });
        map.on('locationfound', (e) => start(e.latlng.lat, e.latlng.lng, true));
        map.on('locationerror', () => {
            // Ditolak / gagal -> pakai lokasi tersimpan pertama, atau Jakarta
            const first = savedLocations[0];
            start(first ? first.lat : DEFAULT_POS[0], first ? first.lng : DEFAULT_POS[1], false);
        });

        let userPos = null;

        function start(lat, lng, isUser) {
            userPos = isUser ? [lat, lng] : null;
            if (isUser) {
                L.circleMarker([lat, lng], { radius: 9, color: '#fff', weight: 3, fillColor: '#3b6fd8', fillOpacity: 1 })
                    .addTo(map).bindPopup('Lokasi kamu');
            }
            map.setView([lat, lng], 13);
            document.getElementById('forecast-place').textContent = isUser ? 'lokasi kamu' : 'lokasi tersimpan (izin lokasi tidak diberikan)';
            loadForecast(lat, lng);
            loadNearby(lat, lng, isUser);
        }

        // ===== 1. Prediksi AQI per jam (Open-Meteo, gratis tanpa API key) =====
        async function loadForecast(lat, lng) {
            try {
                const url = `https://air-quality-api.open-meteo.com/v1/air-quality?latitude=${lat}&longitude=${lng}&hourly=us_aqi&current=us_aqi&timezone=auto&forecast_days=1`;
                const data = await (await fetch(url)).json();

                const hours = data.hourly.time.map(t => t.slice(11, 16));
                const values = data.hourly.us_aqi;

                // Cari rentang 2 jam dengan AQI terendah antara 05.00–20.00
                let best = null;
                for (let i = 0; i < values.length - 1; i++) {
                    const h = parseInt(hours[i]);
                    if (h < 5 || h > 18 || values[i] == null || values[i + 1] == null) continue;
                    const avg = (values[i] + values[i + 1]) / 2;
                    if (!best || avg < best.avg) best = { i, avg };
                }

                if (best) {
                    const lo = Math.min(values[best.i], values[best.i + 1]);
                    const hi = Math.max(values[best.i], values[best.i + 1]);
                    document.getElementById('best-time').textContent = `${hours[best.i]}–${hours[best.i + 2]}`;
                    document.getElementById('best-range').textContent = `AQI ${lo}–${hi} · ${aqiInfo(hi).label}`;
                }

                const nowAqi = data.current?.us_aqi ?? null;
                document.getElementById('now-aqi').textContent = nowAqi ?? '-';
                document.getElementById('now-aqi-label').textContent = `${aqiInfo(nowAqi).label} · skala US AQI`;

                new Chart(document.getElementById('aqiChart'), {
                    type: 'line',
                    data: {
                        labels: hours,
                        datasets: [{
                            label: 'AQI',
                            data: values,
                            borderColor: '#557d00',
                            backgroundColor: 'rgba(130, 163, 128, 0.25)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: values.map((_, i) => best && (i === best.i || i === best.i + 1) ? 5 : 2),
                            pointBackgroundColor: values.map((_, i) => best && (i === best.i || i === best.i + 1) ? '#446900' : '#82a380'),
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'US AQI' } } }
                    }
                });
            } catch (err) {
                document.getElementById('best-range').textContent = 'Prediksi AQI tidak tersedia.';
            }
        }

        // ===== 2. Bandingkan AQI + jarak lokasi tersimpan =====
        async function loadNearby(lat, lng, isUser) {
            const list = document.getElementById('place-list');
            if (!savedLocations.length) {
                list.innerHTML = `<p class="place-meta">Belum ada lokasi tersimpan. <a href="{{ route('locations.index') }}">Tambah lokasi</a>.</p>`;
                return;
            }

            // Ambil AQI semua lokasi sekaligus (1 request)
            let aqis = savedLocations.map(() => null);
            try {
                const lats = savedLocations.map(l => l.lat).join(',');
                const lngs = savedLocations.map(l => l.lng).join(',');
                const data = await (await fetch(`https://air-quality-api.open-meteo.com/v1/air-quality?latitude=${lats}&longitude=${lngs}&current=us_aqi&timezone=auto`)).json();
                const arr = Array.isArray(data) ? data : [data];
                aqis = arr.map(d => d.current?.us_aqi ?? null);
            } catch (err) { /* biarkan null */ }

            const places = savedLocations
                .map((l, i) => ({ ...l, aqi: aqis[i], dist: distanceKm(lat, lng, l.lat, l.lng) }))
                .sort((a, b) => ((a.aqi ?? 999) - (b.aqi ?? 999)) || (a.dist - b.dist));

            list.innerHTML = places.map((p, idx) => {
                const info = aqiInfo(p.aqi);
                const isBest = idx === 0 && p.aqi !== null;
                return `
                    <div class="place-item ${isBest ? 'best' : ''}" data-id="${p.id}">
                        <div>
                            <div class="place-name">${escapeHtml(p.name)}</div>
                            <div class="place-meta">${isUser ? p.dist.toFixed(1) + ' km · ' : ''}AQI ${p.aqi ?? '-'} — ${info.label}</div>
                            ${isBest ? '<div class="place-meta" style="color: var(--accent-green); font-weight: 700;">Udara paling bersih</div>' : ''}
                            <button type="button" class="btn-green btn-small" data-pick="${p.id}">Pilih lokasi ini</button>
                        </div>
                        <div class="aqi-badge ${info.cls}">${p.aqi ?? '-'}<small>${info.label}</small></div>
                    </div>`;
            }).join('');

            // Popup marker ikut menampilkan AQI
            places.forEach(p => markers[p.id]?.setPopupContent(`<b>${escapeHtml(p.name)}</b><br>AQI ${p.aqi ?? '-'} · ${aqiInfo(p.aqi).label}`));

            // Klik kartu -> fokus ke marker di peta
            list.querySelectorAll('.place-item').forEach(item => item.addEventListener('click', () => {
                const m = markers[item.dataset.id];
                if (m) { map.setView(m.getLatLng(), 15); m.openPopup(); }
            }));

            // Klik "Pilih lokasi ini" -> buka Add Entries dengan lokasi terpilih
            list.querySelectorAll('[data-pick]').forEach(btn => btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openAdd(btn.dataset.pick);
            }));
        }

        // ===== Add Entries (layar 1–4) =====
        // Teks sementara. Nanti diganti hasil Gemini.
        const AI_PLACEHOLDER = 'Kondisi di sekitar lokasi ini cukup mendukung untuk berolahraga. '
            + 'Lakukan pemanasan 5–10 menit, bawa air minum, dan cek kualitas udara sebelum mulai. '
            + '(Contoh teks — AI Overview dari Gemini menyusul.)';
        const PICK_HINT = 'Klik penanda lokasi tersimpan atau titik mana saja di peta untuk memilih tempat olahraga.';

        let pickMap = null, detailMap = null, fullMap = null, detailDot = null;
        let selectedId = null;   // lokasi tersimpan yang dipilih
        let freePoint = null;    // titik bebas yang diklik: { lat, lng, name }
        const pickMarkers = {};
        const fullMarkers = {};
        const freeMarkers = {};  // titik bebas di tiap peta (pick / full)
        const conditionCache = {};

        const inputLocation = document.getElementById('input-location');
        const inputLat = document.getElementById('input-lat');
        const inputLng = document.getElementById('input-lng');
        const nameInput = document.getElementById('location_name');
        const durationInput = document.getElementById('duration_minutes');
        const overlayFullmap = document.getElementById('overlay-fullmap');

        function findLocation(id) {
            return savedLocations.find(l => String(l.id) === String(id));
        }

        // Tempat yang sedang dipilih (lokasi tersimpan ATAU titik bebas)
        function currentPlace() {
            const loc = findLocation(selectedId);
            if (loc) return { key: 'loc-' + loc.id, lat: loc.lat, lng: loc.lng, name: loc.name, saved: true };
            if (freePoint) return { key: `pt-${freePoint.lat.toFixed(5)},${freePoint.lng.toFixed(5)}`, ...freePoint, saved: false };
            return null;
        }

        // Buat peta Leaflet; markerStore diisi marker lokasi yang bisa diklik
        function makeMap(elementId, markerStore) {
            const m = L.map(elementId).setView(DEFAULT_POS, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(m);

            if (markerStore) {
                savedLocations.forEach(loc => {
                    markerStore[loc.id] = L.marker([loc.lat, loc.lng])
                        .addTo(m)
                        .bindTooltip(escapeHtml(loc.name))
                        .on('click', () => selectLocation(loc.id));
                });
                if (savedLocations.length) {
                    m.fitBounds(savedLocations.map(l => [l.lat, l.lng]), { padding: [40, 40], maxZoom: 15 });
                }
                // Klik titik mana saja di peta -> pilih titik bebas
                m.on('click', (e) => selectPoint(e.latlng.lat, e.latlng.lng));
            }
            return m;
        }

        function showStep(step) {
            document.getElementById('step-1').style.display = step === 1 ? 'block' : 'none';
            document.getElementById('step-2').style.display = step === 2 ? 'block' : 'none';
        }

        function openAdd(locationId = null) {
            overlayAdd.classList.add('active');
            showStep(1);
            if (!pickMap) pickMap = makeMap('pick-map', pickMarkers);
            setTimeout(() => pickMap.invalidateSize(), 50); // peta di popup perlu dihitung ulang ukurannya
            if (locationId) selectLocation(locationId);
        }

        function highlightMarkers(store) {
            Object.entries(store).forEach(([id, marker]) => {
                const active = (selectedId === null && !freePoint) || String(id) === String(selectedId);
                marker.setOpacity(active ? 1 : 0.4);
            });
        }

        // Gambar / hapus titik bebas (lingkaran hijau) di peta pick & full
        function drawFreePoint() {
            [['pick', pickMap], ['full', fullMap]].forEach(([key, m]) => {
                if (freeMarkers[key]) { freeMarkers[key].remove(); delete freeMarkers[key]; }
                if (m && freePoint) {
                    freeMarkers[key] = L.circleMarker([freePoint.lat, freePoint.lng], { radius: 12, color: '#fff', weight: 3, fillColor: '#557d00', fillOpacity: 1 })
                        .addTo(m)
                        .bindTooltip('Titik pilihan');
                }
            });
        }

        function setSelectedText(name) {
            document.getElementById('ai-sub').textContent = name;
            document.getElementById('selected-place-text').textContent = 'Lokasi dipilih: ' + name;
        }

        // Pilih lokasi tersimpan
        function selectLocation(id) {
            const loc = findLocation(id);
            if (!loc) return;

            selectedId = loc.id;
            freePoint = null;
            inputLocation.value = loc.id;
            inputLat.value = '';
            inputLng.value = '';
            nameInput.value = loc.name;
            nameInput.readOnly = true; // nama lokasi tersimpan tidak diubah dari sini

            setSelectedText(loc.name);
            document.getElementById('ai-text').textContent = AI_PLACEHOLDER;
            drawFreePoint();
            highlightMarkers(pickMarkers);
            highlightMarkers(fullMarkers);
            if (pickMap) pickMap.setView([loc.lat, loc.lng], 15);

            overlayFullmap.classList.remove('active');
            loadConditions(currentPlace()); // mulai ambil AQI & cuaca lebih awal
        }

        // Pilih titik bebas (klik sembarang di peta)
        function selectPoint(lat, lng, name = null) {
            selectedId = null;
            freePoint = { lat, lng, name: name || `Titik ${lat.toFixed(5)}, ${lng.toFixed(5)}` };
            inputLocation.value = '';
            inputLat.value = lat.toFixed(7);
            inputLng.value = lng.toFixed(7);
            nameInput.readOnly = false;
            nameInput.value = name || '';

            setSelectedText(name || 'Mencari nama tempat...');
            document.getElementById('ai-text').textContent = AI_PLACEHOLDER;
            drawFreePoint();
            highlightMarkers(pickMarkers);
            highlightMarkers(fullMarkers);
            if (pickMap) pickMap.setView([lat, lng], Math.max(pickMap.getZoom(), 15));

            overlayFullmap.classList.remove('active');
            loadConditions(currentPlace());
            if (!name) lookupPlaceName(lat, lng);
        }

        // Cari nama tempat dari koordinat (OpenStreetMap Nominatim, reverse geocoding)
        async function lookupPlaceName(lat, lng) {
            let name = null;
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=17&accept-language=id`);
                const data = await res.json();
                name = data.name || (data.display_name ? data.display_name.split(',').slice(0, 2).join(',').trim() : null);
            } catch (err) { /* biarkan nama koordinat */ }

            // Abaikan kalau pengguna sudah memilih tempat lain
            if (!freePoint || freePoint.lat !== lat || freePoint.lng !== lng) return;
            if (name) freePoint.name = name;
            if (!nameInput.value) nameInput.value = freePoint.name;
            setSelectedText(freePoint.name);
        }

        function resetSelection() {
            selectedId = null;
            freePoint = null;
            inputLocation.value = '';
            inputLat.value = '';
            inputLng.value = '';
            nameInput.value = '';
            nameInput.readOnly = false;
            document.getElementById('ai-sub').textContent = 'Pilih lokasi di peta';
            document.getElementById('ai-text').textContent = PICK_HINT;
            document.getElementById('selected-place-text').textContent = 'Pilih lokasi dari daftar di atas atau di form.';
            drawFreePoint();
            highlightMarkers(pickMarkers);
            highlightMarkers(fullMarkers);
            if (pickMap && savedLocations.length) {
                pickMap.fitBounds(savedLocations.map(l => [l.lat, l.lng]), { padding: [40, 40], maxZoom: 15 });
            }
        }

        // Kode cuaca Open-Meteo -> teks
        function weatherLabel(code) {
            if (code === 0) return 'Cerah';
            if (code <= 3) return 'Berawan';
            if (code <= 48) return 'Berkabut';
            if (code <= 67) return 'Hujan';
            if (code <= 77) return 'Salju';
            if (code <= 82) return 'Hujan Deras';
            return 'Badai Petir';
        }

        // AQI + cuaca saat ini dari Open-Meteo (disimpan sementara per tempat)
        function loadConditions(place) {
            if (!conditionCache[place.key]) {
                conditionCache[place.key] = Promise.all([
                    fetch(`https://air-quality-api.open-meteo.com/v1/air-quality?latitude=${place.lat}&longitude=${place.lng}&current=us_aqi&timezone=auto`)
                        .then(r => r.json()).catch(() => null),
                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${place.lat}&longitude=${place.lng}&current=temperature_2m,weather_code&timezone=auto`)
                        .then(r => r.json()).catch(() => null),
                ]).then(([air, wx]) => ({
                    aqi: air?.current?.us_aqi ?? null,
                    weather: wx?.current
                        ? `${weatherLabel(wx.current.weather_code)}, ${Math.round(wx.current.temperature_2m)}°C`
                        : '-',
                }));
            }
            return conditionCache[place.key];
        }

        // Rumus sama dengan ExerciseLogController: durasi x (berat x 3,5 x 6) / 200
        function updateCalories() {
            const minutes = parseInt(durationInput.value);
            document.getElementById('tile-calories').textContent =
                minutes > 0 ? Math.round(minutes * (userWeight * 3.5 * 6) / 200) + ' kkal' : '-';
        }

        async function goToStep2() {
            const place = currentPlace();
            if (!place) {
                document.getElementById('ai-text').textContent = 'Pilih lokasi dulu: klik penanda atau titik mana saja di peta.';
                return;
            }

            showStep(2);
            if (!detailMap) detailMap = makeMap('detail-map', null);
            if (detailDot) detailMap.removeLayer(detailDot);
            detailDot = L.circleMarker([place.lat, place.lng], { radius: 14, color: '#557d00', fillColor: '#557d00', fillOpacity: 1 }).addTo(detailMap);
            setTimeout(() => { detailMap.invalidateSize(); detailMap.setView([place.lat, place.lng], 16); }, 50);

            if (!nameInput.value) nameInput.value = place.name;

            const statsList = document.getElementById('stats-list');
            statsList.innerHTML = '<li>Memuat data lokasi...</li>';
            document.getElementById('tile-weather').textContent = '...';
            document.getElementById('tile-aqi').textContent = '...';
            document.getElementById('tile-aqi-label').textContent = '';
            updateCalories();

            const c = await loadConditions(place);
            const info = aqiInfo(c.aqi);
            const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            statsList.innerHTML = `
                <li><i class="fa-solid fa-bookmark"></i> ${place.saved ? 'Lokasi tersimpan' : 'Titik baru (disimpan otomatis)'}</li>
                <li><i class="fa-solid fa-crosshairs"></i> ${place.lat.toFixed(5)}, ${place.lng.toFixed(5)}</li>
                ${userPos ? `<li><i class="fa-solid fa-route"></i> ${distanceKm(userPos[0], userPos[1], place.lat, place.lng).toFixed(1)} km dari kamu</li>` : ''}
                <li><i class="fa-regular fa-clock"></i> Hari ini, ${time}</li>
                <li><i class="fa-solid fa-wind"></i> Udara: ${info.label}</li>
            `;
            document.getElementById('tile-weather').textContent = c.weather;
            document.getElementById('tile-aqi').textContent = c.aqi ?? '-';
            document.getElementById('tile-aqi-label').textContent = info.label;
        }

        function openFullMap() {
            overlayFullmap.classList.add('active');
            if (!fullMap) fullMap = makeMap('full-map', fullMarkers);
            highlightMarkers(fullMarkers);
            drawFreePoint();
            setTimeout(() => {
                fullMap.invalidateSize();
                const place = currentPlace();
                if (place) fullMap.setView([place.lat, place.lng], 16);
            }, 50);
        }

        // Tombol-tombol
        document.getElementById('btn-open-add').addEventListener('click', () => openAdd());
        document.getElementById('btn-start-session').addEventListener('click', () => openAdd(selectedId));
        document.getElementById('close-add').addEventListener('click', () => overlayAdd.classList.remove('active'));
        document.getElementById('btn-next').addEventListener('click', goToStep2);
        document.getElementById('btn-reset').addEventListener('click', resetSelection);
        document.getElementById('btn-back').addEventListener('click', () => {
            showStep(1);
            setTimeout(() => pickMap.invalidateSize(), 50);
        });
        document.querySelectorAll('[data-expand]').forEach(btn => btn.addEventListener('click', openFullMap));
        document.getElementById('close-fullmap').addEventListener('click', () => overlayFullmap.classList.remove('active'));
        durationInput.addEventListener('input', updateCalories);

        // Saat disimpan: pastikan tempat dipilih & waktu = sekarang
        document.getElementById('entry-form').addEventListener('submit', (e) => {
            if (!inputLocation.value && !inputLat.value) {
                e.preventDefault();
                showStep(1);
                document.getElementById('ai-text').textContent = 'Pilih lokasi dulu: klik penanda atau titik mana saja di peta.';
                return;
            }
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('input-time').value = now.toISOString().slice(0, 16);
        });

        // Layar 3: "ADDED to Entries!" hilang otomatis
        const addedOverlay = document.getElementById('added-overlay');
        if (addedOverlay) {
            const hideAdded = () => {
                addedOverlay.classList.add('hide');
                setTimeout(() => addedOverlay.remove(), 600);
            };
            addedOverlay.addEventListener('click', hideAdded);
            setTimeout(hideAdded, 2000);
        }

        // Kalau validasi gagal, buka lagi Add Entries di layar 2
        if (reopenForm) {
            if (oldLocationId) {
                openAdd(oldLocationId);
                goToStep2();
            } else if (oldPoint) {
                openAdd();
                selectPoint(oldPoint.lat, oldPoint.lng, oldPoint.name);
                goToStep2();
            } else {
                openAdd();
            }
        }
    </script>
</body>
</html>
