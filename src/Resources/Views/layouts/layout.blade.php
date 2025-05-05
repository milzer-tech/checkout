<html>
<head>
    <title>Checkout - @yield('title')</title>
</head>
<body>
    <div>
        <h1>Header</h1>
    </div>
    <div class="container">
        {{ $slot }}
    </div>
    <div>
        <h1>footer</h1>
    </div>
</body>
</html>
