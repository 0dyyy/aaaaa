<nav id="navbar">
    <a href="{{ url('/') }}" class="logo">ASVIS</a>
    <ul class="nav-links">
        <li>
            <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>
        </li>
        <li>
            <a href="{{ route('food-logs.index') }}" class="{{ request()->routeIs('food-logs.*') ? 'active' : '' }}">Food Tracker</a>
        </li>
        <li>
            <a href="{{ route('exercise-logs.index') }}" class="{{ request()->routeIs('exercise-logs.*') ? 'active' : '' }}">Exercise</a>
        </li>
        <li>
            <a href="{{ route('health-scores.index') }}" class="{{ request()->routeIs('health-scores.*') ? 'active' : '' }}">Summary</a>
        </li>

        @auth
            @if(Auth::user()->isAdmin())
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}" style="color: #ff9900;">Admin Dashboard</a>
                </li>
            @endif
        @endauth
    </ul>

    <div class="user-profile">
        @auth
            <a href="{{ route('profile.edit') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;" title="Edit Profil & Fisik">
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
                <div class="user-avatar" style="display: flex; align-items: center; justify-content: center; background-color: var(--primary-brown); color: var(--bg-color); font-weight: bold;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}" style="margin-left: 10px;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 1.1rem;" title="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        @else
            <div class="user-info">
                <a href="{{ route('login') }}" style="margin-right: 15px; text-decoration: none; color: {{ request()->routeIs('login') ? 'var(--primary-brown)' : '#333' }}; font-weight: {{ request()->routeIs('login') ? 'bold' : 'normal' }};">Login</a>
                <a href="{{ route('register') }}" style="text-decoration: none; color: {{ request()->routeIs('register') ? 'var(--primary-brown)' : '#333' }}; font-weight: bold;">Register</a>
            </div>
        @endguest
    </div>
</nav>