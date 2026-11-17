<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASVIS - Food Tracker</title>
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- NAVBAR (Assuming standard layout) -->
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

    <!-- HERO SECTION -->
    <section class="tracker-hero">
        <!-- Left: Chart -->
        <div class="hero-left">
            <div class="chart-placeholder">
                <div class="chart-label">
                    <small>Daily intakes</small>
                    <h3>FOOD</h3>
                </div>
                <!-- SVG mimicking the green circular chart in mockup -->
                <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                    <path d="M 50 10 A 40 40 0 1 1 15 70" fill="none" stroke="#87c700" stroke-width="12" stroke-linecap="round"/>
                    <circle cx="50" cy="50" r="10" fill="none" stroke="#87c700" stroke-width="8"/>
                    <line x1="56" y1="56" x2="80" y2="80" stroke="#87c700" stroke-width="12" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <!-- Center: Greeting & Buttons -->
        <div class="hero-center">
            <h1>Hi <em>User</em></h1>
            <p>adadawdadawd</p>
            
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
                        <i class="fa-regular fa-comment-dots"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Stats -->
        <div class="hero-right">
            <div class="stats-card">
                <h2>Stats</h2>
                <ul class="stats-list">
                    <li>a</li>
                    <li>AA</li>
                    <li>A</li>
                    <li>A</li>
                    <li>a</li>
                    <li>A</li>
                    <li>A</li>
                    <li>AA</li>
                    <li>A</li>
                </ul>
            </div>
        </div>
    </section>

    <h2 class="page-title">FOOD TRACKER</h2>

    <!-- CALENDAR STRIP (Bottom) -->
    <div class="calendar-strip">
        <div class="cal-day-header">Mo</div>
        <div class="cal-day-header">Tu</div>
        <div class="cal-day-header">We</div>
        <div class="cal-day-header">Th</div>
        <div class="cal-day-header">Fr</div>
        <div class="cal-day-header">Sa</div>
        <div class="cal-day-header">Su</div>

        <div class="cal-date">15</div>
        <div class="cal-date">16</div>
        <div class="cal-date active-day-start">17</div>
        <div class="cal-date active-day-end">18</div>
        <div class="cal-date">19</div>
        <div class="cal-date">20</div>
        <div class="cal-date">21</div>
    </div>


    <!-- OVERLAY 1: FULL CALENDAR -->
    <div class="overlay" id="overlay-calendar">
        <div class="modal-content">
            <div class="modal-nav">
                <div class="modal-month">May 2023</div>
                <div class="close-modal" id="close-calendar">Tutup kalender</div>
            </div>
            
            <div class="full-calendar-grid">
                <!-- Headers -->
                <div class="full-cal-header">Mo</div>
                <div class="full-cal-header">Tu</div>
                <div class="full-cal-header">We</div>
                <div class="full-cal-header">Th</div>
                <div class="full-cal-header">Fr</div>
                <div class="full-cal-header">Sa</div>
                <div class="full-cal-header">Su</div>
                
                <!-- Row 1 -->
                <div class="full-cal-cell">1</div>
                <div class="full-cal-cell">2</div>
                <div class="full-cal-cell">3</div>
                <div class="full-cal-cell">4</div>
                <div class="full-cal-cell">5</div>
                <div class="full-cal-cell">6</div>
                <div class="full-cal-cell">7</div>
                
                <!-- Row 2 -->
                <div class="full-cal-cell">8</div>
                <div class="full-cal-cell">9</div>
                <div class="full-cal-cell">10</div>
                <div class="full-cal-cell">11</div>
                <div class="full-cal-cell">12</div>
                <div class="full-cal-cell">13</div>
                <div class="full-cal-cell">14</div>

                <!-- Row 3 -->
                <div class="full-cal-cell">15</div>
                <div class="full-cal-cell">16</div>
                <div class="full-cal-cell active-start">17</div>
                <div class="full-cal-cell active-end">18</div>
                <div class="full-cal-cell">19</div>
                <div class="full-cal-cell">20</div>
                <div class="full-cal-cell">21</div>

                <!-- Row 4 -->
                <div class="full-cal-cell">22</div>
                <div class="full-cal-cell">23</div>
                <div class="full-cal-cell">24</div>
                <div class="full-cal-cell">25</div>
                <div class="full-cal-cell">26</div>
                <div class="full-cal-cell">27</div>
                <div class="full-cal-cell">28</div>

                <!-- Row 5 -->
                <div class="full-cal-cell">29</div>
                <div class="full-cal-cell">30</div>
                <div class="full-cal-cell">31</div>
                <div class="full-cal-cell empty-month">1</div>
                <div class="full-cal-cell empty-month">2</div>
                <div class="full-cal-cell empty-month">3</div>
                <div class="full-cal-cell empty-month">4</div>
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

            <!-- Original Form Route targeted -->
            <form action="{{ route('food-logs.store') }}" method="POST" enctype="multipart/form-data" id="food-upload-form">
                @csrf
                <div class="add-entry-layout">
                    <!-- Left: Upload Box -->
                    <div class="upload-section" id="upload-trigger">
                        <div class="upload-placeholder" id="upload-placeholder">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                        <img src="" alt="Preview" class="preview-image" id="preview-image">
                        <div class="preview-text" id="preview-text">anggap aja<br>gambar<br>makanan</div>
                        
                        <!-- Hidden Inputs -->
                        <input type="file" id="photo-upload" name="photo" accept="image/*" required>
                        <!-- Fallback inputs for the original controller requirements -->
                        <input type="hidden" name="food_name" id="hidden_food_name" value="Food Image Upload">
                        <input type="hidden" name="consumed_at" id="hidden_consumed_at">
                    </div>

                    <!-- Right: Info & Results -->
                    <div class="info-section">
                        <h2 class="info-title" id="info-title">Add The Image</h2>
                        
                        <!-- Hidden until image is added -->
                        <div id="ai-results-container">
                            <h2 class="info-title" style="display:none;" id="entries-taker-title">Entries Taker</h2>
                            
                            <div class="ai-overview-card" id="ai-overview-card">
                                <h3>AI Overview</h3>
                                <ul class="stats-list" id="ai-stats-list">
                                    <li>a</li>
                                    <li>AA</li>
                                    <li>A</li>
                                    <li>A</li>
                                    <li>a</li>
                                    <li>A</li>
                                    <li>A</li>
                                    <li>AA</li>
                                    <li>A</li>
                                </ul>
                            </div>

                            <div class="action-icon-buttons" id="action-buttons">
                                <button type="submit" class="icon-btn" title="Submit Entry"><i class="fa-solid fa-check"></i></button>
                                <button type="button" class="icon-btn" id="btn-retry" title="Retry Upload"><i class="fa-solid fa-rotate-left"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Set current time for hidden input required by original controller
        document.getElementById('hidden_consumed_at').value = new Date().toISOString().slice(0, 16);

        // Overlay Logic
        const btnCalendar = document.getElementById('btn-open-calendar');
        const btnAdd = document.getElementById('btn-open-add');
        const overlayCalendar = document.getElementById('overlay-calendar');
        const overlayAdd = document.getElementById('overlay-add');
        const closeCalendar = document.getElementById('close-calendar');
        const closeAdd = document.getElementById('close-add');

        btnCalendar.addEventListener('click', () => overlayCalendar.classList.add('active'));
        closeCalendar.addEventListener('click', () => overlayCalendar.classList.remove('active'));

        btnAdd.addEventListener('click', () => overlayAdd.classList.add('active'));
        closeAdd.addEventListener('click', () => {
            overlayAdd.classList.remove('active');
            resetUploadState(); // Reset form when closed
        });

        // Close on clicking outside modal content
        [overlayCalendar, overlayAdd].forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if(e.target === overlay) overlay.classList.remove('active');
            });
        });

        // Upload Logic
        const uploadTrigger = document.getElementById('upload-trigger');
        const fileInput = document.getElementById('photo-upload');
        const placeholder = document.getElementById('upload-placeholder');
        const previewImage = document.getElementById('preview-image');
        const previewText = document.getElementById('preview-text');
        
        const infoTitle = document.getElementById('info-title');
        const entriesTitle = document.getElementById('entries-taker-title');
        const aiCard = document.getElementById('ai-overview-card');
        const actionBtns = document.getElementById('action-buttons');
        const btnRetry = document.getElementById('btn-retry');

        // Click box to trigger file selection
        uploadTrigger.addEventListener('click', () => {
            if(!fileInput.value) fileInput.click();
        });

        // Handle file selection
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update visual state matching image_565889.png
                    placeholder.style.display = 'none';
                    // Optional: You can display the actual image, or keep the mockup text
                    // previewImage.src = e.target.result;
                    // previewImage.style.display = 'block';
                    previewText.style.display = 'block';

                    // Update Right Side Info
                    infoTitle.style.display = 'none';
                    entriesTitle.style.display = 'inline-block';
                    aiCard.style.display = 'block';
                    actionBtns.style.display = 'flex';
                    
                    // Set filename as the food name for the controller
                    document.getElementById('hidden_food_name').value = this.files[0].name;
                }.bind(this);
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Retry button
        btnRetry.addEventListener('click', (e) => {
            e.stopPropagation(); // prevent clicking the upload box behind it
            resetUploadState();
            fileInput.click(); // immediately open file selector again
        });

        function resetUploadState() {
            fileInput.value = '';
            placeholder.style.display = 'block';
            previewImage.style.display = 'none';
            previewImage.src = '';
            previewText.style.display = 'none';
            
            infoTitle.style.display = 'inline-block';
            entriesTitle.style.display = 'none';
            aiCard.style.display = 'none';
            actionBtns.style.display = 'none';
        }
    </script>
</body>
</html>