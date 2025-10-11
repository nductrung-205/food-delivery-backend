<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        try {
            Log::info('=== ProductController@index START ===');
            
            // Test database connection
            DB::connection()->getPdo();
            Log::info('✅ Database connected');
            
            // Count products
            $count = Product::count();
            Log::info("📊 Total products in database: {$count}");
            
            // Get products with category
            $products = Product::with('category')->get();
            Log::info("✅ Products retrieved: {$products->count()}");

            // Add image URLs
            foreach ($products as $product) {
                if ($product->image) {
                    $product->image_url = asset('storage/' . $product->image);
                }
            }

            Log::info('=== ProductController@index SUCCESS ===');
            return response()->json($products, 200);
            
        } catch (\PDOException $e) {
            Log::error('❌ DATABASE ERROR in ProductController@index');
            Log::error('Message: ' . $e->getMessage());
            Log::error('Code: ' . $e->getCode());
            
            return response()->json([
                'message' => 'Lỗi kết nối database',
                'error' => $e->getMessage()
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('❌ ERROR in ProductController@index');
            Log::error('Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Lỗi server khi lấy danh sách sản phẩm',
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // GET /api/products/{id}
    public function show($id)
    {
        try {
            Log::info("=== Getting product ID: {$id} ===");
            
            $product = Product::with('category')->findOrFail($id);

            if ($product->image) {
                $product->image_url = asset('storage/' . $product->image);
            }

            Log::info("✅ Product found: {$product->name}");
            return response()->json($product);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("⚠️  Product not found: ID {$id}");
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error("❌ Error getting product {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi server',
                'error' => $e->getMessage()
            ], 500);
        }
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
            Log::info('=== Creating new product ===');
            Log::info('Data: ' . json_encode($validated));
            
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
                Log::info("✅ Image uploaded: {$path}");
            }

            $product = Product::create($validated);
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;

            Log::info("✅ Product created: ID {$product->id}");
            return response()->json($product, 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('⚠️  Validation failed: ' . json_encode($e->errors()));
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('❌ Error creating product: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Lỗi khi thêm sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        try {
            Log::info("=== Updating product ID: {$id} ===");
            
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

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                    Log::info("🗑️  Old image deleted: {$product->image}");
                }

                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
                Log::info("✅ New image uploaded: {$path}");
            }

            $product->update($validated);
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;

            Log::info("✅ Product updated: ID {$product->id}");
            return response()->json($product);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("⚠️  Product not found for update: ID {$id}");
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error("❌ Error updating product {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi khi cập nhật sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        try {
            Log::info("=== Deleting product ID: {$id} ===");
            
            $product = Product::findOrFail($id);

            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
                Log::info("🗑️  Image deleted: {$product->image}");
            }

            $product->delete();

            Log::info("✅ Product deleted: ID {$id}");
            return response()->json([
                'message' => 'Xóa sản phẩm thành công'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("⚠️  Product not found for deletion: ID {$id}");
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error("❌ Error deleting product {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi khi xóa sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}