<!-- CSS Libraries -->
@if(in_array('bootstrap', $libraries ?? []))
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endif

<!-- JavaScript Libraries -->
@if(in_array('jQuery', $libraries ?? []))
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endif

