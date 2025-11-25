<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    @yield('title')
    <link rel="stylesheet" href="{{ asset('css/nav-bar.css') }}">
    @yield('styles')
    @yield('admin-styles')
</head>

<body>
    <div class="admin-dashboard">
        <aside class="admin-sidebar">
            <img src="{{ asset('img/background_sidebar.png') }}" class="background-img" alt="Background">

            <div class="admin-logo" style="z-index: 1; position: relative;">
                <img class="logo_img" src="{{ asset('img/Logo.png') }}" alt="Verde Hub Dashboard">
            </div>

            <hr class="divider" style="z-index: 1; position: relative;">

            <nav class="admin-nav" style="z-index: 1; position: relative;">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('img/side_logo/dashboard.png') }}"> Dashboard
                </a>

                <a href="{{ route('bin.manager') }}">
                    <img src="{{ asset('img/side_logo/bin_map.png') }}"> Bin Manager
                </a>

                <a href="{{ route('reports') }}">
                    <img src="{{ asset('img/side_logo/analytics.png') }}"> Reports
                </a>

                <a href="{{ route('utility.index') }}">
                    <img src="{{ asset('img/side_logo/utility.png') }}"> Utility Manager
                </a>

                <a href="{{ route('account.manager') }}">
                    <img src="{{ asset('img/side_logo/account-manager.png') }}"> Account Manager
                </a>

                {{-- ✅ New Ranking Manager Section --}}
                <a href="{{ route('college.manager') }}">
                    <img src="{{ asset('img/side_logo/ranking.png') }}" alt="Ranking Manager Icon">
                    Ranking Manager
                </a>

                <a href="{{ route('settings.index') }}">
                    <img src="{{ asset('img/side_logo/settings.png') }}">
                    System Settings
                </a>
            </nav>

            <hr class="divider" style="z-index: 1; position: relative;">

            <nav class="admin-nav" style="z-index: 1; position: relative;">
                @auth
                    <span style="font-weight: 600; color: #a5f3fc; margin-left: 5px;">
                        ({{ Auth::user()->name }})
                    </span>
                @endauth
                <a href="{{ route('account.settings') }}">
                    <img src="{{ asset('img/side_logo/account.png') }}">
                    Account Settings
                </a>
            </nav>

            <hr class="divider" style="z-index: 1; position: relative;">

            <form method="POST" action="{{ route('logout') }}" style="display:inline; z-index: 1; position: relative;">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to logout?')"
                    style="cursor:pointer;">
                    Logout
                </button>
            </form>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>@yield('admin-title', 'Welcome back, Fritz!')</h1>
                <div class="search-container">
                    <input type="text" class="search-bar" placeholder="Search...">
                    <img src="{{ asset('img/search.png') }}" alt="Search" class="search-icon">
                    <img src="{{ asset('img/notifications.png') }}" alt="Notifications" class="notification-icon"
                        style="cursor:pointer;">
                    @include('notification')
                </div>
            </header>

            <hr class="divider2">

            <section class="admin-content">
                @yield('admin-content')
            </section>
        </main>
    </div>
    @stack('scripts')
</body>

<script>
    window.addEventListener('load', function () {
        const offlineBanner = document.createElement('div');
        offlineBanner.id = 'offline-banner';
        offlineBanner.textContent = '⚠️ No internet connection. Can’t load data.';
        offlineBanner.style.cssText = `
            display: none;
            background-color: #ff4c4c;
            color: white;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
        `;
        document.body.appendChild(offlineBanner);

        function updateOnlineStatus() {
            offlineBanner.style.display = navigator.onLine ? 'none' : 'block';
            if (!navigator.onLine && typeof stopLiveData === 'function') stopLiveData();
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
    });
</script>

</html>