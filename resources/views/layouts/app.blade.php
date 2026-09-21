<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Shop') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f4f4f5; color: #18181b; }
        .container { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
        nav { background: #18181b; color: #fff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 1rem; }
        nav .brand { font-weight: 700; font-size: 1.2rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
        .card { background: #fff; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .card h3 { margin-bottom: .5rem; }
        .price { font-weight: 700; color: #16a34a; }
        .btn { display: inline-block; background: #18181b; color: #fff; border: none; padding: .6rem 1.2rem; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: .95rem; margin-top: .75rem; }
        .btn:hover { background: #3f3f46; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 1.5rem; }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #e4e4e7; }
        input[type=text], input[type=email], input[type=number] { padding: .5rem; border: 1px solid #d4d4d8; border-radius: 6px; width: 100%; }
        .alert { background: #dcfce7; color: #166534; padding: .75rem 1rem; border-radius: 6px; margin-top: 1rem; }
        .error { color: #dc2626; font-size: .85rem; margin-top: .25rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .25rem; font-weight: 600; }
    </style>
</head>
<body>
    <nav>
        <a class="brand" href="{{ route('products.index') }}">{{ config('app.name', 'Shop') }}</a>
        <div>
            <a href="{{ route('products.index') }}">Products</a>
            <a href="{{ route('cart.index') }}">Cart ({{ array_sum(session('cart', [])) }})</a>
        </div>
    </nav>
    <div class="container">
        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif
        @yield('content')
    </div>
</body>
</html>
