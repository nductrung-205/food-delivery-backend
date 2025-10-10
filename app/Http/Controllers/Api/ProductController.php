<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
{
    try {
        $products = Product::with('category')->get();

        foreach ($products as $product) {
            if ($product->image) {
                $product->image_url = asset('storage/' . $product->image);
            }
        }

        return response()->json($products);
    } catch (\Exception $e) {
        Log::error('❌ Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage());
        return response()->json(['message' => 'Lỗi server'], 500);
    }
}

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        if ($product->image) {
            $product->image_url = asset('storage/' . $product->image);
        }

        return response()->json($product);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'stock'       => 'nullable|integer|min:0',
            'status'      => 'nullable|in:available,unavailable',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,jfif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
            }

            $product = Product::create($validated);
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;

            return response()->json($product, 201);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm sản phẩm'], 500);
        }
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'price'       => 'sometimes|required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'sometimes|nullable|string',
            'stock'       => 'nullable|integer|min:0',
            'status'      => 'nullable|in:available,unavailable',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,jfif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
            }

            $product->update($validated);
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;

            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật sản phẩm'], 500);
        }
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa sản phẩm'], 500);
        }
    }
}