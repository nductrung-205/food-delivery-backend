<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function myOrders(Request $request)
    {
        $user = $request->user();

        $orders = Order::with('orderItems.product')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($orders);
    }


    public function show($id)
    {
        try {
            $order = Order::with('orderItems.product', 'user')->findOrFail($id);

            $authenticatedUserId = Auth::id();


            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            $isAdmin = false;
            if ($user) {
                $isAdmin = $user->isAdmin();
            }

            foreach ($order->orderItems as $item) {
                if ($item->product && $item->product->image) {
                    $item->product->image_url = asset('storage/' . $item->product->image);
                } else {
                    $item->product->image_url = asset('images/placeholder.jpg');
                }
            }


            if (!Auth::check() || (!$isAdmin && $order->user_id !== $authenticatedUserId)) {
                Log::warning("Unauthorized attempt to view order.", [
                    'order_id' => $id,
                    'authenticated_user_id' => $authenticatedUserId
                ]);
                return response()->json(['message' => 'Unauthorized to view this order'], 403);
            }

            return response()->json($order);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order not found for ID: {$id}", ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            Log::error("Error fetching order detail for ID: {$id}. " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            return response()->json(['message' => 'An internal server error occurred.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
                'payment_method' => 'required|string|in:COD,Banking',

            ]);

            $user = $request->user();

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $validatedData['total_price'],
                'status' => 'pending',
                'payment_method' => $validatedData['payment_method'],
                'ordered_at' => now(),

            ]);

            foreach ($validatedData['items'] as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }


            $order->load('user', 'orderItems.product');

            $responseData = [
                'id' => $order->id,
                'total' => $order->total_price,
                'customer' => [
                    'id' => $user->id,
                    'name' => $user->fullname,
                    'email' => $user->email,

                    'address' => $request->customer['address'] ?? 'Địa chỉ tạm thời (cần thêm vào DB)',
                    'ward' => $request->customer['ward'] ?? 'Phường tạm thời',
                    'district' => $request->customer['district'] ?? 'Quận tạm thời',
                    'city' => $request->customer['city'] ?? 'Thành phố tạm thời',
                ],
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product->name ?? 'Sản phẩm không rõ',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                })->toArray(),
            ];

            return response()->json($responseData, 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error storing order: ' . $e->getMessage(), ['request_data' => $request->all(), 'exception' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to place order.'], 500);
        }
    }

    public function index()
    {

        return response()->json(Order::with('orderItems.product', 'user')->get());
    }

    public function updateStatus(Request $request, $id)
    {

        try {
            $request->validate([
                'status' => 'required|string|in:pending,confirmed,delivered,cancelled',
            ]);

            $order = Order::findOrFail($id);
            $order->status = $request->status;
            $order->save();

            return response()->json([
                'message' => 'Order status updated successfully.',
                'order' => $order->load('orderItems.product', 'user')
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage(), ['order_id' => $id, 'request_data' => $request->all()]);
            return response()->json(['message' => 'Failed to update order status.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            $validatedData = $request->validate([
                'total_price' => 'sometimes|numeric|min:0',
                'payment_method' => 'sometimes|string|in:COD,Banking',
                'status' => 'sometimes|string|in:pending,confirmed,delivered,cancelled',
                'items' => 'sometimes|array',
                'items.*.product_id' => 'required_with:items|exists:products,id',
                'items.*.quantity' => 'required_with:items|integer|min:1',
                'items.*.price' => 'required_with:items|numeric|min:0',
            ]);

            $order->update([
                'total_price' => $validatedData['total_price'] ?? $order->total_price,
                'payment_method' => $validatedData['payment_method'] ?? $order->payment_method,
                'status' => $validatedData['status'] ?? $order->status,
            ]);

            // Nếu có cập nhật items
            if (isset($validatedData['items'])) {
                $order->orderItems()->delete();

                foreach ($validatedData['items'] as $item) {
                    $order->orderItems()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }

            $order->load('orderItems.product', 'user');

            return response()->json([
                'message' => '✅ Cập nhật đơn hàng thành công',
                'order' => $order,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage(), ['order_id' => $id]);
            return response()->json(['message' => 'Lỗi khi cập nhật đơn hàng'], 500);
        }
    }

    public function destroy($id)
    {

        try {
            $order = Order::findOrFail($id);
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage(), ['order_id' => $id]);
            return response()->json(['message' => 'Failed to delete order.'], 500);
        }
    }

    public function cancelOrder(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $user = $request->user();

            if ($order->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized to cancel this order'], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Chỉ có thể hủy đơn hàng đang chờ xử lý.'
                ], 400);
            }

            $order->status = 'cancelled';
            $order->save();

            $order->load('orderItems.product');

            foreach ($order->orderItems as $item) {
                if ($item->product && $item->product->image) {
                    $item->product->image_url = asset('storage/' . $item->product->image);
                } else {
                    $item->product->image_url = asset('images/placeholder.jpg');
                }
            }
         

            return response()->json([
                'message' => 'Đơn hàng đã được hủy thành công.',
                'order' => $order 
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage(), ['order_id' => $id, 'user_id' => $request->user()->id]);
            return response()->json(['message' => 'Failed to cancel order.'], 500);
        }
    }
}
