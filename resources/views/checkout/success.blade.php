@extends('layouts.app')

@section('content')
    <div class="card" style="max-width:600px;margin-top:1rem">
        <h1>Order Placed!</h1>
        <p style="margin:.75rem 0">Thanks, {{ $order->customer_name }}. Your order #{{ $order->id }} for <strong>${{ number_format($order->total, 2) }}</strong> has been received.</p>
        <a class="btn" href="{{ route('products.index') }}">Continue Shopping</a>
    </div>
@endsection
