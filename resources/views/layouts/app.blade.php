<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>@yield('title', 'Product API Manager')</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- Scripts Needed before render to guard page / check theme -->
    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    
    <script>
        const currentTheme = localStorage.getItem('tablerTheme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        
        function toggleTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('tablerTheme', theme);
            document.querySelectorAll('.hide-theme-dark').forEach(el => el.style.display = theme === 'dark' ? 'none' : 'block');
            document.querySelectorAll('.hide-theme-light').forEach(el => el.style.display = theme === 'light' ? 'none' : 'block');
        }
    </script>
</head>
<body>
    <div class="page">
        <!-- Sidebar Navigation -->
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <!-- Mobile toggler -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                        <i class="ti ti-package text-primary fs-2 me-2"></i>
                        <span class="text-white fw-bold">Product API</span>
                    </a>
                </h1>
                
                <!-- Navigation Menu -->
                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item @yield('active-dashboard')">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-dashboard"></i>
                                </span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item @yield('active-products')">
                            <a class="nav-link" href="{{ route('products.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-package"></i>
                                </span>
                                <span class="nav-link-title">Products</span>
                            </a>
                        </li>
                        <li class="nav-item @yield('active-create-product')">
                            <a class="nav-link" href="{{ route('products.create') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-plus"></i>
                                </span>
                                <span class="nav-link-title">Add Product</span>
                            </a>
                        </li>
                        <li class="nav-item mt-auto">
                            <a class="nav-link text-danger" href="#" id="logout-btn">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-logout"></i>
                                </span>
                                <span class="nav-link-title">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <div class="page-wrapper">
            <!-- Top Navbar header -->
            <header class="navbar navbar-expand-md navbar-light d-none d-lg-flex d-print-none navbar-glass py-2">
                <div class="container-xl">
                    <div class="navbar-nav ms-auto flex-row align-items-center gap-3">
                        <!-- Dark/Light Theme -->
                        <div class="d-flex me-2">
                            <a href="#" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip" data-bs-placement="bottom" onclick="toggleTheme('dark'); return false;">
                                <i class="ti ti-moon fs-2"></i>
                            </a>
                            <a href="#" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip" data-bs-placement="bottom" onclick="toggleTheme('light'); return false;">
                                <i class="ti ti-sun fs-2"></i>
                            </a>
                        </div>
                        
                        <!-- User Profile dropdown -->
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                                <span class="avatar avatar-sm avatar-initials rounded-circle user-avatar-placeholder">U</span>
                                <div class="d-none d-xl-block ps-2">
                                    <div class="user-name-placeholder fw-semibold">Loading...</div>
                                    <div class="user-email-placeholder mt-1 small text-secondary">...</div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <a href="{{ route('products.index') }}" class="dropdown-item">View Products</a>
                                <a href="{{ route('products.create') }}" class="dropdown-item">Add Product</a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item text-danger" id="profile-logout-btn">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Content Slot -->
            <div class="page-body">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const current = localStorage.getItem('tablerTheme') || 'light';
            toggleTheme(current);

            // Wire up top profile logout
            const profileLogout = document.getElementById('profile-logout-btn');
            if (profileLogout) {
                profileLogout.addEventListener('click', (e) => {
                    e.preventDefault();
                    auth.logout();
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
