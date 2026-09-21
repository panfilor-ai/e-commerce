@extends('layouts.app')

@section('content')
    <h1>Products</h1>
    <div class="grid">
        @forelse ($products as $product)
            <div class="card">
                <h3><a href="{{ route('products.show', $product) }}" style="color:inherit">{{ $product->name }}</a></h3>
                <p>{{ Str::limit($product->description, 80) }}</p>
                <p class="price">${{ number_format($product->price, 2) }}</p>
                <p><small>{{ $product->stock }} in stock</small></p>
                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <button class="btn" type="submit" {{ $product->stock < 1 ? 'disabled' : '' }}>Add to Cart</button>
                </form>
            </div>
        @empty
            <p>No products yet. Run <code>php artisan db:seed</code>.</p>
        @endforelse
    </div>
    <div style="margin-top:1.5rem">{{ $products->links() }}</div>
@endsection
