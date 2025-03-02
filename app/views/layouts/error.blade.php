<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error')</title>

    <!-- Include Bootstrap & Other Libraries -->
    @include('partials.master_include', ['libraries' => ['bootstrap', 'jQuery']])

    @yield('styles')
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

    <div class="container text-center">
        <div class="bg-white shadow-lg rounded p-5 mx-auto" style="max-width: 600px;">
            <h1 class="display-3 fw-bold text-danger">@yield('status', 'Error')</h1>
            <h2 class="fs-4 fw-semibold text-dark">@yield('message', 'Something went wrong.')</h2>
            <p class="text-secondary">@yield('description', 'Please try again later or contact support.')</p>
            <a href="/dashboard" class="btn btn-primary mt-3">Go Home</a>
        </div>
    </div>

    
    
    <script>
        (() => { @yield('init') })();
        $(() => { @yield('document_ready') });
        @yield('script')
    </script>

</body>
</html>
