<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'required|image',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'image|required',
        ]);

        $updateData = $request->only(['name', 'description']);

        if ($request->has('name')) {
            $updateData['slug'] = \Str::slug($request->name);
        }

        if ($request->hasFile('image')) {
            // Удаляем старое изображение
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
            $updateData['image'] = $imagePath;
        }

        $category->update($updateData);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        return response()->json(null, 204);
    }
}
