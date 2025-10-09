<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // GET /api/users
    public function index()
    {
        $users = User::all();

        foreach ($users as $user) {
            $user->role_label = $user->role == 0 ? 'Admin' : 'User';
        }

        return response()->json($users);
    }

    // GET /api/users/{id}
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->role_label = $user->role == 0 ? 'Admin' : 'User';

            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('Error fetching user: ' . $e->getMessage());
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }
    }

    // POST /api/users
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'nullable|in:0,1', // 0 = admin, 1 = user
        ]);

        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['role'] = $validated['role'] ?? 1;

            $user = User::create($validated);
            $user->role_label = $user->role == 0 ? 'Admin' : 'User';

            return response()->json([
                'message' => '✅ Tạo người dùng thành công',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tạo người dùng'], 500);
        }
    }

    // PUT /api/users/{id}
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'fullname' => 'sometimes|required|string|max:255',
                'email'    => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6',
                'role'     => 'nullable|in:0,1',
            ]);

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);
            $user->role_label = $user->role == 0 ? 'Admin' : 'User';

            return response()->json([
                'message' => '✅ Cập nhật người dùng thành công',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật người dùng'], 500);
        }
    }

    // DELETE /api/users/{id}
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['message' => '✅ Đã xóa người dùng']);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa người dùng'], 500);
        }
    }
}