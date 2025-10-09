<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Thêm facade Storage để quản lý file

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/categories
     */
    public function index()
    {
        // Bạn có thể tải các danh mục con nếu cần hiển thị theo cấu trúc cây
        // Hoặc chỉ đơn giản là trả về tất cả
        return response()->json(Category::all());
        // Hoặc với phân trang:
        // return response()->json(Category::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/categories
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validation cho hình ảnh
            'parent_id'   => 'nullable|exists:categories,id', // parent_id phải tồn tại trong bảng categories
        ]);

        if ($request->hasFile('image')) {
            // Lưu ảnh vào thư mục 'public/categories' và lấy đường dẫn
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = $imagePath;
        }

        $category = Category::create($validated);

        return response()->json($category, 201); // Trả về mã 201 Created
    }

    /**
     * Display the specified resource.
     * GET /api/categories/{id}
     */
    public function show($id)
    {
        // Tải danh mục và có thể cả danh mục cha và con nếu cần
        $category = Category::with(['parent', 'children'])->findOrFail($id);
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/categories/{id}
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);

        // Xử lý hình ảnh mới
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            // Lưu ảnh mới
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = $imagePath;
        }
        // Nếu client gửi 'image' = null hoặc một chuỗi rỗng để xóa ảnh hiện tại
        else if ($request->has('image') && ($request->input('image') === null || $request->input('image') === '')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = null;
        }


        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/categories/{id}
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Xóa file ảnh liên quan đến danh mục này
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}