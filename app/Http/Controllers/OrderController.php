<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items.product')->get();
        return response()->json($orders);
    }

    public function allOrders()
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::with(['user', 'items.product'])->get();
        return response()->json($orders);
    }

        public function store(Request $request)
        {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Проверка наличия товаров
        $items = collect($request->items);
        $products = Product::whereIn('id', $items->pluck('product_id'))->get();

        $total = 0;
        $orderItems = [];

        foreach ($items as $item) {
            $product = $products->firstWhere('id', $item['product_id']);

            if ($product->quantity < $item['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock for product: ' . $product->name
                ], 400);
            }

            $price = $product->sale_price ?? $product->price;
            $total += $price * $item['quantity'];

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $price,
            ];

            // Уменьшаем количество товара на складе
            $product->decrement('quantity', $item['quantity']);
            if ($product->quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
        }

        // Создаем заказ
        $order = Order::create([
            'user_id' => $request->user()->id,
            'order_number' => 'ORD-' . Str::upper(Str::random(10)),
            'status' => 'pending',
            'total' => $total,
            'item_count' => count($orderItems),
            'payment_method' => $request->payment_method,
            'is_paid' => false,
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address,
            'notes' => $request->notes,
        ]);

        // Добавляем товары в заказ
        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

            return response()->json($order->load(['items.product', 'payment', 'shipping']), 201);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return response()->json($order->load('items.product'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    public function userOrders(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.product', 'shipping'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }
}
