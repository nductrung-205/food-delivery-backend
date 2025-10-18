<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cloudinary\Cloudinary; // Thêm dòng này để sử dụng lớp Cloudinary chính
// use Cloudinary\Uploader; // Xóa hoặc comment dòng này

class ImageController extends Controller
{
    protected $cloudinary; // Khai báo thuộc tính để lưu trữ đối tượng Cloudinary

    public function __construct()
    {
        // Khởi tạo Cloudinary client ở đây.
        // Configuration::instance đã được gọi trong CloudinaryServiceProvider
        // nên chúng ta chỉ cần tạo một instance của Cloudinary.
        $this->cloudinary = new Cloudinary();
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            try {
                $uploadedFile = $request->file('image');

                // Sử dụng đối tượng Cloudinary đã được khởi tạo để tải ảnh lên
                $result = $this->cloudinary->uploadApi()->upload($uploadedFile->getRealPath(), [
                    'folder' => 'my_app_images' // Optional: folder to store images in Cloudinary
                ]);

                $imageUrl = $result['secure_url'];

                return response()->json([
                    'message' => 'Image uploaded successfully!',
                    'image_url' => $imageUrl
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image upload failed!',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json(['message' => 'No image provided'], 400);
    }
}