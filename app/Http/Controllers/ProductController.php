<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        try {
            // Валидация данных
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'material' => 'required|string',
                'color' => 'required|string',
                'dimensions' => 'required|string',
                'images' => 'image|required',
            ]);

            \Log::info('Начало обработки запроса', [
                'request_data' => $request->all(),
                'files' => $request->hasFile('images') ? 'Есть файлы' : 'Нет файлов'
            ]);

            // Обработка изображений
            $imagesPaths = [];
            if ($request->hasFile('images')) {
                $image = $request->file('images');
                $path = $image->store('uploads', 'public');
                $imagesPaths[] = $path;
            }

            // Создание продукта
            $productData = [
                'name' => $validatedData['name'],
                'slug' => \Str::slug($validatedData['name']),
                'category_id' => $validatedData['category_id'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'sale_price' => $validatedData['sale_price'] ?? null,
                'quantity' => $validatedData['quantity'],
                'material' => $validatedData['material'],
                'color' => $validatedData['color'],
                'dimensions' => $validatedData['dimensions'],
                'images' => $imagesPaths,
                'in_stock' => $validatedData['quantity'] > 0,
            ];

            $product = Product::create($productData);
            \Log::info('Продукт успешно создан', ['product_id' => $product->id]);

            return response()->json($product, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Ошибка валидации', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Ошибка при создании продукта', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Для отладки в консоли (если это консольная команда)
            if (app()->runningInConsole()) {
                echo "Error: " . $e->getMessage() . PHP_EOL;
                echo $e->getTraceAsString() . PHP_EOL;
            }

            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category', 'reviews.user'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'material' => 'sometimes|string',
            'color' => 'sometimes|string',
            'dimensions' => 'sometimes|json',
            'images' => 'sometimes|array',
        ]);

        $updateData = $request->only([
            'name', 'category_id', 'description', 'price', 'sale_price',
            'quantity', 'material', 'color', 'dimensions'
        ]);

        if ($request->has('name')) {
            $updateData['slug'] = \Str::slug($request->name);
        }

        if ($request->has('quantity')) {
            $updateData['in_stock'] = $request->quantity > 0;
        }

        if ($request->hasFile('images')) {
            // Удаляем старые изображения
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }

            $imagesPaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagesPaths[] = $path;
            }
            $updateData['images'] = $imagesPaths;
        }

        $product->update($updateData);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        // Удаляем изображения
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image);
        }

        $product->delete();
        return response()->json(null, 204);
    }

    public function productsByCategory(Category $category)
    {
        $products = $category->products()->with('category')->get();
        return response()->json($products);
    }

    public function search($query)
    {
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->with('category')
            ->get();

        return response()->json($products);
    }
}
