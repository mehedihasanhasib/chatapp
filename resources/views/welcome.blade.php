<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Client</title>
</head>

<body>
    @if (Auth::user())
        <a href="/dashboard">Dashboard</a>
    @else
        <a href="/login">Login</a>
        <a href="register">Register</a>
    @endif
</body>

</html>
