<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('products.index');
        }

        $items = collect($cart)->map(fn ($qty, $id) => [
            'product' => Product::find($id),
            'quantity' => $qty,
        ])->filter(fn ($item) => $item['product']);

        $total = $items->sum(fn ($item) => $item['product']->price * $item['quantity']);

        return view('checkout.index', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('products.index');
        }

        $order = DB::transaction(function () use ($cart, $validated) {
            $items = collect($cart)->map(fn ($qty, $id) => [
                'product' => Product::lockForUpdate()->find($id),
                'quantity' => $qty,
            ])->filter(fn ($item) => $item['product']);

            $order = Order::create([
                ...$validated,
                'total' => $items->sum(fn ($i) => $i['product']->price * $i['quantity']),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['product']->price,
                ]);
                $item['product']->decrement('stock', min($item['quantity'], $item['product']->stock));
            }

            return $order;
        });

        session()->forget('cart');

        return view('checkout.success', compact('order'));
    }
}
