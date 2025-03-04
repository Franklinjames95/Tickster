<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Default Title')</title>
    
    <!-- Include Master Include File -->
    @include('partials.master_include', ['libraries' => ['bootstrap', 'jQuery']])
    
    <!-- Page-Specific Styles -->
    @yield('styles')

</head>
<body>

    <header>        
        @hasSection('header')
            <h1 class="fw-bold">@yield('header')</h1>
        @else
            @include('components.navs.navbar', [
                'logo' => '/images/logo.png',
                'navItems' => [
                    ['name' => 'Dashboard', 'route' => '/dashboard'],
                    ['name' => 'Team', 'route' => '/team'],
                    ['name' => 'Projects', 'route' => '/projects'],
                    ['name' => 'Calendar', 'route' => '/calendar'],
                    ['name' => 'Reports', 'route' => '/reports'],
                ],
                'activePage' => $_SERVER['REQUEST_URI'],
                'userAvatar' => '/images/user-avatar.png'
            ])
        @endHasSection
    </header>

    <main>
        
        @yield('content')
    </main>

    <footer>
        <p>@yield('footer', '@ 2025 Tickster')</p>
    </footer>

    <script>
        (() => { @yield('init') })();
        $(() => { @yield('document_ready') });
        @yield('script')
    </script>

</body>
</html>
