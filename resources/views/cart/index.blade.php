@extends('layouts.app')

@section('content')
    <h1>Your Cart</h1>
    @if ($items->isEmpty())
        <p style="margin-top:1rem">Your cart is empty. <a href="{{ route('products.index') }}">Browse products</a>.</p>
    @else
        <table>
            <thead>
                <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['product']->name }}</td>
                        <td>${{ number_format($item['product']->price, 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.update', $item['product']) }}" style="display:flex;gap:.5rem">
                                @csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" style="width:70px">
                                <button class="btn" style="margin:0" type="submit">Update</button>
                            </form>
                        </td>
                        <td>${{ number_format($item['product']->price * $item['quantity'], 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                                @csrf @method('DELETE')
                                <button class="btn" style="margin:0;background:#dc2626" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <h2 style="margin-top:1.5rem">Total: ${{ number_format($total, 2) }}</h2>
        <a class="btn" href="{{ route('checkout.index') }}">Proceed to Checkout</a>
    @endif
@endsection
