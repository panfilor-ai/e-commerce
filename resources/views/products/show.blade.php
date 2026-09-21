@extends('layouts.app')

@section('content')
    <div class="card" style="max-width:600px">
        <h1>{{ $product->name }}</h1>
        <p style="margin:.75rem 0">{{ $product->description }}</p>
        <p class="price" style="font-size:1.5rem">${{ number_format($product->price, 2) }}</p>
        <p>{{ $product->stock }} in stock</p>
        <form method="POST" action="{{ route('cart.add', $product) }}">
            @csrf
            <div class="form-group" style="max-width:120px">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}">
            </div>
            <button class="btn" type="submit" {{ $product->stock < 1 ? 'disabled' : '' }}>Add to Cart</button>
        </form>
    </div>
@endsection
