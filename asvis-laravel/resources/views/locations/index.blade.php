<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Lokasi Olahraga</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #map { width: 100%; height: 420px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: relative; z-index: 0; }
    </style>
</head>
<body>

    <nav id="navbar">
        <a href="{{ url('/') }}" class="logo">ASVIS</a>
        <ul class="nav-links">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="{{ route('food-logs.index') }}">Food Tracker</a></li>
            <li><a href="{{ route('exercise-logs.index') }}">Exercise</a></li>
            <li><a href="{{ route('health-scores.index') }}">Summary</a></li>
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

    <section class="summary-header">
        <h1>Lokasi Olahraga</h1>
        <div class="main-score" style="font-size: 8rem;"><i class="fa-solid fa-map-location-dot"></i></div>
        <p>Tambahkan lokasi favoritmu untuk memantau kualitas udara saat berolahraga.</p>
    </section>

    <main class="summary-content">
        @if(session('success'))
            <div style="background-color: var(--light-green); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div style="background-color: #ff4d4d; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <ul style="list-style-position: inside;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bottom-row" style="grid-template-columns: 1fr;">
            <div class="large-suggestion" style="background-color: white; color: var(--text-dark); border: 2px solid var(--primary-brown);">
                <h2 style="color: var(--primary-brown);">Peta Lokasi</h2>
                <div id="map"></div>

                <form method="POST" action="{{ route('locations.store') }}" style="margin-top: 20px;">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nama Lokasi</label>
                        <input type="text" name="name" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: var(--font-sans);" placeholder="Contoh: Gelora Bung Karno" value="{{ old('name') }}" required>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Alamat (Opsional)</label>
                        <input type="text" name="address" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: var(--font-sans);" placeholder="Contoh: Senayan, Jakarta Pusat" value="{{ old('address') }}">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Latitude (Klik Peta)</label>
                            <input type="text" id="latitude" name="latitude" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; font-family: var(--font-sans);" value="{{ old('latitude') }}" readonly required>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Longitude (Klik Peta)</label>
                            <input type="text" id="longitude" name="longitude" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; font-family: var(--font-sans);" value="{{ old('longitude') }}" readonly required>
                        </div>
                    </div>

                    <button type="submit" style="background-color: var(--primary-brown); color: white; border: none; padding: 12px 25px; border-radius: 5px; font-family: var(--font-sans); font-weight: bold; cursor: pointer; font-size: 1rem;"><i class="fa-solid fa-floppy-disk"></i> Simpan Lokasi</button>
                </form>
            </div>
        </div>

        <div class="suggestion-text" style="padding: 0; margin-top: 40px; margin-bottom: 20px;">
            <h2>Daftar Lokasi Tersimpan</h2>
        </div>

        <div style="background-color: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--primary-brown); color: white; text-align: left;">
                    <tr>
                        <th style="padding: 15px;">Nama Tempat</th>
                        <th style="padding: 15px;">Alamat</th>
                        <th style="padding: 15px;">Koordinat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;"><strong>{{ $location->name }}</strong></td>
                            <td style="padding: 15px;">{{ $location->address ?? '-' }}</td>
                            <td style="padding: 15px; font-size: 13px; color: #666;">Lat: {{ $location->latitude }}<br>Lng: {{ $location->longitude }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding: 30px; text-align: center; color: #888;">Belum ada lokasi. Klik peta untuk menambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 10) { nav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)'; } 
            else { nav.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)'; }
        });

        // Data lokasi dari database
        const locations = @json($locations);
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 1. Buat peta, pusat awal di Jakarta
        const map = L.map('map').setView([-6.2, 106.816], 12);

        // 2. Ambil gambar peta dari OpenStreetMap (gratis, tanpa API key)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // 3. Tampilkan marker untuk lokasi yang sudah tersimpan
        locations.forEach(loc => {
            if (loc.latitude && loc.longitude) {
                L.marker([parseFloat(loc.latitude), parseFloat(loc.longitude)])
                    .addTo(map)
                    .bindPopup(`<b>${loc.name}</b><br>${loc.address ?? ''}`);
            }
        });

        // 4. Klik peta untuk mengisi latitude & longitude di form
        let picked = null;
        map.on('click', (e) => {
            document.getElementById('latitude').value = e.latlng.lat.toFixed(7);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(7);

            if (picked) map.removeLayer(picked);
            picked = L.marker(e.latlng).addTo(map).bindPopup('Lokasi Baru').openPopup();
        });
    </script>
</body>
</html>
