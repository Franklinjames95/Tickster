<nav class="navbar navbar-expand-lg px-3" data-bs-theme="dark">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="#">
            <img src="{{ $logo ?? '/images/logo.png' }}" alt="Logo" class="rounded">
        </a>

        <!-- Navbar toggler for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-3">
                @foreach ($navItems as $item)
                    <li class="nav-item">
                        <a class="nav-link {{ $activePage == $item['route'] ? 'active bg-opacity-10 rounded px-3 py-2' : '' }}" href="{{ $item['route'] }}">
                            {{ $item['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Right Side Icons -->
        <div class="d-flex align-items-center">
            <i class="bi bi-bell fs-5 me-3"></i> <!-- Notification Bell -->
            <img src="{{ $userAvatar ?? '/images/user-avatar.png' }}" alt="User" class="rounded-circle border" width="36" height="36">

            <!-- Theme Toggle Button -->
            <button class="btn btn-outline-light ms-3" id="theme-toggle">
                <span id="theme-icon">🌙</span>
            </button>
        </div>
    </div>
</nav>

@dummy <script> @enddummy

@section('document_ready')
@parent
    const htmlElement = $("html");
    const themeToggle = $("#theme-toggle");
    const themeIcon = $("#theme-icon");

    // Load saved theme or default to light
    let savedTheme = localStorage.getItem("theme") || "light";
    htmlElement.attr("data-bs-theme", savedTheme);
    themeIcon.text(savedTheme === "dark" ? "☀️" : "🌙");

    // Toggle theme on button click
    themeToggle.click(function () {
        let newTheme = htmlElement.attr("data-bs-theme") === "dark" ? "light" : "dark";

        htmlElement.attr("data-bs-theme", newTheme);
        localStorage.setItem("theme", newTheme);
        themeIcon.text(newTheme === "dark" ? "☀️" : "🌙");
    });
@endsection

@dummy </script> @enddummy
