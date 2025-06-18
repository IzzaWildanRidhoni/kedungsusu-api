<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;
use App\Helpers\ApiResponse;

class OrderController extends Controller
{
    public function show($id)
    {
        $order = Order::with('product')->find($id);

        if (!$order) {
            return ApiResponse::error(['message' => 'Order not found'], 404);
        }

        return ApiResponse::success([
            'data' => $order
        ], 'Order detail');
    }


    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order = Order::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'order_id' => 'ORDER-' . time(),
            'amount' => $product->price,
            'status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_id,
                'gross_amount' => $order->amount,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return ApiResponse::success([
            'order' => $order,
            'snap_token' => $snapToken,
        ], 'Order created, snap token generated');
    }

    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed != $request->signature_key) {
            return ApiResponse::error(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_id', $request->order_id)->first();

        if (!$order) {
            return ApiResponse::error(['message' => 'Order not found'], 404);
        }

        if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
            $order->update(['status' => 'paid']);
        } elseif ($request->transaction_status == 'cancel' || $request->transaction_status == 'expire') {
            $order->update(['status' => 'failed']);
        }

        return ApiResponse::success(['message' => 'Order status updated']);
    }
}
