<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        try {
            Log::info('=== ProductController@index START ===');
            
            DB::connection()->getPdo();
            Log::info('✅ Database connected');
            
            $count = Product::count();
            Log::info("📊 Total products in database: {$count}");
            
            $products = Product::with('category')->get();
            Log::info("✅ Products retrieved: {$products->count()}");

            // Không cần thêm image_url nữa vì đã lưu full URL từ Cloudinary
            
            Log::info('=== ProductController@index SUCCESS ===');
            return response()->json($products, 200);
            
        } catch (\PDOException $e) {
            Log::error('❌ DATABASE ERROR in ProductController@index');
            Log::error('Message: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Lỗi kết nối database',
                'error' => $e->getMessage()
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('❌ ERROR in ProductController@index');
            Log::error('Message: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Lỗi server khi lấy danh sách sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/products/{id}
    public function show($id)
    {
        try {
            Log::info("=== Getting product ID: {$id} ===");
            
            $product = Product::with('category')->findOrFail($id);

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
            
            // Upload ảnh lên Cloudinary
            if ($request->hasFile('image')) {
                $uploadedFile = Cloudinary::upload(
                    $request->file('image')->getRealPath(),
                    [
                        'folder' => 'products',
                        'resource_type' => 'image',
                        'transformation' => [
                            'width' => 800,
                            'height' => 800,
                            'crop' => 'limit',
                            'quality' => 'auto'
                        ]
                    ]
                );

                $validated['image'] = $uploadedFile->getSecurePath();
                $validated['cloudinary_public_id'] = $uploadedFile->getPublicId();
                
                Log::info("✅ Image uploaded to Cloudinary: {$validated['image']}");
            }

            $product = Product::create($validated);

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

            // Upload ảnh mới lên Cloudinary
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ trên Cloudinary nếu có
                if ($product->cloudinary_public_id) {
                    try {
                        Cloudinary::destroy($product->cloudinary_public_id);
                        Log::info("🗑️  Old image deleted from Cloudinary: {$product->cloudinary_public_id}");
                    } catch (\Exception $e) {
                        Log::warning("⚠️  Could not delete old image: " . $e->getMessage());
                    }
                }

                // Upload ảnh mới
                $uploadedFile = Cloudinary::upload(
                    $request->file('image')->getRealPath(),
                    [
                        'folder' => 'products',
                        'resource_type' => 'image',
                        'transformation' => [
                            'width' => 800,
                            'height' => 800,
                            'crop' => 'limit',
                            'quality' => 'auto'
                        ]
                    ]
                );

                $validated['image'] = $uploadedFile->getSecurePath();
                $validated['cloudinary_public_id'] = $uploadedFile->getPublicId();
                
                Log::info("✅ New image uploaded to Cloudinary: {$validated['image']}");
            }

            $product->update($validated);

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

            // Xóa ảnh trên Cloudinary nếu có
            if ($product->cloudinary_public_id) {
                try {
                    Cloudinary::destroy($product->cloudinary_public_id);
                    Log::info("🗑️  Image deleted from Cloudinary: {$product->cloudinary_public_id}");
                } catch (\Exception $e) {
                    Log::warning("⚠️  Could not delete image: " . $e->getMessage());
                }
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