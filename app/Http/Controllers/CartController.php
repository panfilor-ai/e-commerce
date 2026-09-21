<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $items = collect($cart)->map(fn ($qty, $id) => [
            'product' => Product::find($id),
            'quantity' => $qty,
        ])->filter(fn ($item) => $item['product']);

        $total = $items->sum(fn ($item) => $item['product']->price * $item['quantity']);

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $quantity;
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', "{$product->name} added to cart.");
    }

    public function update(Request $request, Product $product)
    {
        $quantity = (int) $request->input('quantity', 1);
        $cart = session('cart', []);

        if ($quantity <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $quantity;
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index');
    }

    public function remove(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Item removed.');
    }
}
