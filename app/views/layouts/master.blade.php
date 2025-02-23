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
        <h1>@yield('header', 'Welcome to Tickster')</h1>
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
