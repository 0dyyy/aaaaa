<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Food Tracker</title>
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <x-navbar />

    @php
        $today = now()->toDateString();
        
        // Group ALL logs by date for the calendar and modal
        $logsByDate = $foodLogs->groupBy(function($log) {
            return \Carbon\Carbon::parse($log->consumed_at)->format('Y-m-d');
        });

        $todayLogs = $foodLogs->filter(fn($log) => \Carbon\Carbon::parse($log->consumed_at)->toDateString() === $today);
        
        $totalCals = $todayLogs->sum('calories');
        $totalProtein = $todayLogs->sum(fn($log) => $log->nutrition_details['protein_g'] ?? 0);
        $totalCarbs = $todayLogs->sum(fn($log) => $log->nutrition_details['carbs_g'] ?? 0);
        $totalFat = $todayLogs->sum(fn($log) => $log->nutrition_details['fat_g'] ?? 0);
        $totalEntries = $todayLogs->count();
    @endphp

    <!-- HERO SECTION -->
    <section class="tracker-hero">
        <div class="hero-left">
            <div class="chart-placeholder">
                <div class="chart-label">
                    <small>Daily intakes</small>
                    <h3>FOOD</h3>
                </div>
                <canvas id="foodDailyChart"></canvas>
            </div>
        </div>

        <div class="hero-center">
            <h1>Hi <em>{{ Auth::user()->name }}</em></h1>
            <p>Hari ini kamu mencatat {{ $totalEntries }} makanan.</p>
            
            <div class="action-buttons">
                <div class="action-btn-group">
                    <p>Calender</p>
                    <button class="btn-box" id="btn-open-calendar">
                        <i class="fa-regular fa-calendar"></i>
                    </button>
                </div>
                <div class="action-btn-group">
                    <p>Add Entries</p>
                    <button class="btn-box" id="btn-open-add">
                        <i class="fa-solid fa-comment-dots"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="hero-right">
            <div class="stats-card">
                <h2>Stats Today</h2>
                <ul class="stats-list">
                    <li>Total Kalori <span class="val">{{ $totalCals }} kcal</span></li>
                    <li>Protein <span class="val">{{ $totalProtein }} g</span></li>
                    <li>Karbohidrat <span class="val">{{ $totalCarbs }} g</span></li>
                    <li>Lemak <span class="val">{{ $totalFat }} g</span></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- SUCCESS MESSAGE -->
    @if(session('success'))
        <div style="background-color: var(--btn-green); color: var(--primary-brown); padding: 15px; text-align: center; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div style="background:#ffb347;color:#4a4238;padding:15px;text-align:center;font-weight:bold;">
            {{ session('warning') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#ff4d4d;color:white;padding:15px;text-align:center;">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <h2 class="page-title">FOOD TRACKER</h2>

    <!-- CALENDAR STRIP -->
    @php $startOfWeek = now()->startOfWeek(); @endphp
    <div class="calendar-strip">
        <div class="cal-day-header">Mo</div>
        <div class="cal-day-header">Tu</div>
        <div class="cal-day-header">We</div>
        <div class="cal-day-header">Th</div>
        <div class="cal-day-header">Fr</div>
        <div class="cal-day-header">Sa</div>
        <div class="cal-day-header">Su</div>

        @for($i = 0; $i < 7; $i++)
            @php 
                $date = $startOfWeek->copy()->addDays($i);
                $dateString = $date->format('Y-m-d');
                $isToday = $date->isToday();
                $hasLogs = $logsByDate->has($dateString);
            @endphp
            <div class="cal-date {{ $hasLogs ? 'active-day-start cal-day-clickable' : '' }} {{ $isToday ? 'current-day' : '' }}" 
                 @if($hasLogs) data-date="{{ $dateString }}" @endif>
                {{ $date->format('d') }}
            </div>
        @endfor
    </div>

    <!-- OVERLAY 1: FULL CALENDAR -->
    <div class="overlay" id="overlay-calendar">
        <div class="modal-content">
            <div class="modal-nav">
                <div class="modal-month">{{ now()->format('F Y') }}</div>
                <div class="close-modal" id="close-calendar">Tutup kalender</div>
            </div>
            
            <div class="full-calendar-grid">
                <div class="full-cal-header">Mo</div>
                <div class="full-cal-header">Tu</div>
                <div class="full-cal-header">We</div>
                <div class="full-cal-header">Th</div>
                <div class="full-cal-header">Fr</div>
                <div class="full-cal-header">Sa</div>
                <div class="full-cal-header">Su</div>
                
                @php
                    $firstDayOfMonth = now()->startOfMonth();
                    $lastDayOfMonth = now()->endOfMonth();
                    $startOffset = $firstDayOfMonth->dayOfWeekIso - 1; 
                @endphp

                @for($i = 0; $i < $startOffset; $i++)
                    <div class="full-cal-cell empty-month"></div>
                @endfor

                @for($day = 1; $day <= $lastDayOfMonth->day; $day++)
                    @php 
                        $currentDate = $firstDayOfMonth->copy()->addDays($day - 1);
                        $dateString = $currentDate->format('Y-m-d');
                        $hasLogs = $logsByDate->has($dateString);
                        $isToday = $currentDate->isToday();
                    @endphp
                    
                    <div class="full-cal-cell {{ $hasLogs ? 'active-start cal-day-clickable' : '' }} {{ $isToday ? 'current-day' : '' }}" 
                         @if($hasLogs) data-date="{{ $dateString }}" @endif>
                        {{ $day }}
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- OVERLAY 1.5: LOG DETAILS (Shows when clicking a green date) -->
    <div class="overlay" id="overlay-details">
        <div class="modal-content" style="max-width: 800px; background-color: var(--hero-bg);">
            <div class="modal-nav">
                <div class="modal-month" id="details-date-title" style="font-family: var(--font-serif); font-size: 2rem; color: var(--primary-brown);"></div>
                <div class="close-modal" id="close-details"><i class="fa-solid fa-xmark"></i> Tutup</div>
            </div>
            
            <div class="log-details-list" id="log-details-list">
                <!-- Log cards will be injected here via JavaScript -->
            </div>
        </div>
    </div>

    <!-- OVERLAY 2: ADD ENTRY -->
    <div class="overlay" id="overlay-add">
        <div class="modal-content">
            <div class="modal-nav">
                <div></div> <!-- Spacer -->
                <div class="close-modal" id="close-add">Tutup</div>
            </div>

            <form action="{{ route('food-logs.store') }}" method="POST" enctype="multipart/form-data" id="food-upload-form">
                @csrf
                <div class="add-entry-layout">
                    <div class="upload-section" id="upload-trigger">
                        <div class="upload-placeholder" id="upload-placeholder">
                            <i class="fa-solid fa-cube"></i>
                            <p style="margin-top: 10px; font-size: 1rem; color: #888;">Klik untuk unggah foto (opsional)</p>
                        </div>
                        <img src="" alt="Preview" class="preview-image" id="preview-image">
                        
                        <input type="file" id="photo-upload" name="photo" accept="image/*">
                        <input type="hidden" name="consumed_at" id="hidden_consumed_at">
                    </div>

                    <div class="info-section">
                        <h2 class="info-title" id="info-title">Catat Makanan</h2>
                        
                        <div style="margin-bottom: 20px; width: 100%;">
                            <label for="food_name_input" style="display: block; font-weight: bold; margin-bottom: 8px; color: var(--primary-brown);">
                                Nama / Deskripsi Makanan
                            </label>
                            <input
                                type="text"
                                id="food_name_input"
                                name="food_name"
                                placeholder="Contoh: Nasi goreng ayam + telur dadar (kosongkan jika hanya foto)"
                                style="width: 100%; padding: 12px 15px; border: 2px solid var(--primary-brown); border-radius: 10px; font-family: var(--font-sans); font-size: 1rem;"
                            >
                        </div>

                        <div id="ai-results-container">
                            <div class="ai-overview-card" id="ai-overview-card" style="display: block; min-height: auto;">
                                <h3>Ready to Analyze</h3>
                                <ul class="stats-list">
                                    <li style="text-align: center; color: white;">Isi deskripsi makanan, unggah foto, atau keduanya.</li>
                                    <li style="text-align: center; color: white;">Klik tombol centang di bawah ini untuk mengirim ke AI Gemini.</li>
                                </ul>
                            </div>

                            <div class="action-icon-buttons" id="action-buttons" style="display: flex;">
                                <button type="submit" class="icon-btn" title="Submit & Analyze"><i class="fa-solid fa-check"></i></button>
                                <button type="button" class="icon-btn" id="btn-retry" title="Reset Form"><i class="fa-solid fa-rotate-left"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Pass backend logs to JS safely -->
    <script>
        const allFoodLogs = @json($logsByDate);
        const csrfToken = '{{ csrf_token() }}';
    </script>

    <script>
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('hidden_consumed_at').value = now.toISOString().slice(0, 16);

        // Chart.js Setup
        const ctx = document.getElementById('foodDailyChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Lemak', 'Protein', 'Karbohidrat'],
                datasets: [{
                    data: [{{ $totalFat }}, {{ $totalProtein }}, {{ $totalCarbs }}], 
                    backgroundColor: ['#325c00', '#69a800', '#447500'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });

        // Modal Elements
        const overlayCalendar = document.getElementById('overlay-calendar');
        const overlayAdd = document.getElementById('overlay-add');
        const overlayDetails = document.getElementById('overlay-details');

        // Open/Close Main Modals
        document.getElementById('btn-open-calendar').addEventListener('click', () => overlayCalendar.classList.add('active'));
        document.getElementById('close-calendar').addEventListener('click', () => overlayCalendar.classList.remove('active'));

        document.getElementById('btn-open-add').addEventListener('click', () => overlayAdd.classList.add('active'));
        document.getElementById('close-add').addEventListener('click', () => { overlayAdd.classList.remove('active'); resetUploadState(); });
        
        document.getElementById('close-details').addEventListener('click', () => overlayDetails.classList.remove('active'));

        // Handle Clicking on a Green Date (Both in strip and full calendar)
        document.querySelectorAll('.cal-day-clickable').forEach(cell => {
            cell.addEventListener('click', function() {
                const dateStr = this.getAttribute('data-date');
                if (!dateStr || !allFoodLogs[dateStr]) return;

                const logsForDay = allFoodLogs[dateStr];
                
                // Format Date for Title (e.g., "17 November 2026")
                const dateObj = new Date(dateStr);
                const options = { day: 'numeric', month: 'long', year: 'numeric' };
                document.getElementById('details-date-title').innerText = dateObj.toLocaleDateString('id-ID', options);

                const listContainer = document.getElementById('log-details-list');
                listContainer.innerHTML = ''; // Clear previous

                logsForDay.forEach(log => {
                    // Safety checks for JSON data
                    const p = log.nutrition_details?.protein_g ?? 0;
                    const c = log.nutrition_details?.carbs_g ?? 0;
                    const f = log.nutrition_details?.fat_g ?? 0;
                    const comment = log.nutrition_details?.ai_comment ?? 'Tidak ada komentar AI.';
                    
                    // Determine image path (use first photo if exists, else placeholder)
                    let imgPath = log.photos && log.photos.length > 0 
                        ? `/storage/${log.photos[0].photo_path}` 
                        : 'https://via.placeholder.com/80?text=No+Img';

                    // Format Time
                    const time = new Date(log.consumed_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

                    // Build HTML Card
                    const cardHTML = `
                        <div class="log-card">
                            <div class="log-card-header">
                                <div class="log-card-title">${log.food_name}</div>
                                <div class="log-card-time"><i class="fa-regular fa-clock"></i> ${time}</div>
                            </div>
                            <div class="log-card-body">
                                <img src="${imgPath}" class="log-card-image" alt="Food image">
                                <div class="log-card-info">
                                    <p><strong><i class="fa-solid fa-fire"></i> ${log.calories} kcal</strong></p>
                                    <p><em>" ${comment} "</em></p>
                                    <div class="log-card-macros">
                                        <span>Protein: ${p}g</span>
                                        <span>Karbo: ${c}g</span>
                                        <span>Lemak: ${f}g</span>
                                    </div>
                                </div>
                                <div>
                                    <form action="/tracker-makanan/${log.id}" method="POST" onsubmit="return confirm('Hapus makanan ini?');">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn-delete-log"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                    listContainer.insertAdjacentHTML('beforeend', cardHTML);
                });

                // Hide full calendar if open, show details modal
                overlayCalendar.classList.remove('active');
                overlayDetails.classList.add('active');
            });
        });

        // Upload UI Logic
        const uploadTrigger = document.getElementById('upload-trigger');
        const fileInput = document.getElementById('photo-upload');
        const placeholder = document.getElementById('upload-placeholder');
        const previewImage = document.getElementById('preview-image');
        const foodNameInput = document.getElementById('food_name_input');
        const btnRetry = document.getElementById('btn-retry');

        uploadTrigger.addEventListener('click', () => { if(!fileInput.value) fileInput.click(); });

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    placeholder.style.display = 'none';
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                }.bind(this);
                reader.readAsDataURL(this.files[0]);
            }
        });

        btnRetry.addEventListener('click', (e) => {
            e.stopPropagation();
            resetUploadState();
        });

        function resetUploadState() {
            fileInput.value = '';
            foodNameInput.value = '';
            placeholder.style.display = 'block';
            previewImage.style.display = 'none';
            previewImage.src = '';
        }
    </script>
</body>
</html>