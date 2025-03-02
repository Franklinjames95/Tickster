@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-sm-6 col-md-4">
                <div class="card">
                    <div class="card-header text-center">Login</div>
                    <div class="card-body">
                        @if(isset($error))
                            <div class="alert alert-danger">
                                Error received: {{ $error }}
                            </div>
                        @endif
                        <form method="POST" action="{{ $webapp->route('login.post') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
