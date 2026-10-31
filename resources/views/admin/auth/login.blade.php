<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in · Tanui Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body class="admin">
    <main class="container py-5" style="max-width: 32rem">
        <p class="sim-label">Demonstration access</p>
        <h1 class="mt-3">Administration</h1>
        <p>Sign in with <code>admin@tanuicommerce.test</code> / <code>password</code>. Public registration is disabled.</p>
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form class="card card-body" method="post" action="{{ route('admin.login.attempt') }}">
            @csrf
            <label class="form-label" for="email">Email</label>
            <input class="form-control mb-3" id="email" type="email" name="email" value="{{ old('email') }}" required>
            <label class="form-label" for="password">Password</label>
            <input class="form-control mb-3" id="password" type="password" name="password" required>
            <button class="btn btn-dark">Sign in</button>
        </form>
    </main>
</body>
</html>
