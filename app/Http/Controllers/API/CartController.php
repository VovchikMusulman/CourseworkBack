<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Auth::user()->cart()->with('product')->get();
        return response()->json($cartItems);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        // Проверяем, есть ли уже этот товар в корзине
        $cartItem = $user->cart()->where('product_id', $product->id)->first();

        if ($cartItem) {
            // Если товар уже есть - обновляем количество
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Если нет - создаем новую запись
            $cartItem = new Cart([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->sale_price ?? $product->price
            ]);
            $user->cart()->save($cartItem);
        }

        return response()->json($cartItem->load('product'), 201);
    }

    public function update(Request $request, Cart $cartItem)
    {
        $this->authorize('update', $cartItem);

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem->update([
            'quantity' => $request->quantity
        ]);

        return response()->json($cartItem->load('product'));
    }

    public function remove(Cart $cartItem)
    {
        $this->authorize('delete', $cartItem);

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }
}
