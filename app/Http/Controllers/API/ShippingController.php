<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index()
    {
        $shippings = Shipping::all();
        return response()->json($shippings);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'status' => 'sometimes|string',
        ]);

        $shipping = Shipping::create($request->all());
        return response()->json($shipping, 201);
    }

    public function show(Shipping $shipping)
    {
        return response()->json($shipping);
    }

    public function update(Request $request, Shipping $shipping)
    {
        $request->validate([
            'address' => 'sometimes|string',
            'status' => 'sometimes|string',
        ]);

        $shipping->update($request->all());
        return response()->json($shipping);
    }

    public function destroy(Shipping $shipping)
    {
        $shipping->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Shipping $shipping)
    {
        $request->validate([
            'status' => 'required|in:pending,shipped,delivered,cancelled',
        ]);

        $shipping->update(['status' => $request->status]);

        return response()->json($shipping);
    }
}
