<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ReturnModel;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index()
    {
        $returns = ReturnModel::with('order')->get();
        return response()->json($returns);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|string',
            'reason' => 'required|string',
        ]);

        $return = ReturnModel::create($request->all());
        return response()->json($return, 201);
    }

    public function show(ReturnModel $return)
    {
        return response()->json($return->load('order'));
    }

    public function update(Request $request, ReturnModel $return)
    {
        $request->validate([
            'status' => 'sometimes|string',
            'reason' => 'sometimes|string',
        ]);

        $return->update($request->all());
        return response()->json($return);
    }

    public function destroy(ReturnModel $return)
    {
        $return->delete();
        return response()->json(null, 204);
    }
}
