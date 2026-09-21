@extends('layouts.app')

@section('content')
    <h1>Checkout</h1>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:1.5rem">
        <div class="card">
            <h2 style="margin-bottom:1rem">Your Details</h2>
            <form method="POST" action="{{ route('checkout.store') }}">
                @csrf
                <div class="form-group">
                    <label for="customer_name">Name</label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                    @error('customer_name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="customer_email">Email</label>
                    <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}" required>
                    @error('customer_email') <p class="error">{{ $message }}</p> @enderror
                </div>
                <button class="btn" type="submit">Place Order</button>
            </form>
        </div>
        <div class="card">
            <h2 style="margin-bottom:1rem">Order Summary</h2>
            @foreach ($items as $item)
                <p>{{ $item['product']->name }} × {{ $item['quantity'] }} — ${{ number_format($item['product']->price * $item['quantity'], 2) }}</p>
            @endforeach
            <h3 style="margin-top:1rem">Total: ${{ number_format($total, 2) }}</h3>
        </div>
    </div>
@endsection
