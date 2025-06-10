<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;

class ProductController extends Controller
{
    public function index()
    {
        return ApiResponse::success(Product::all(), 'Product list retrieved');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255|unique:products,name',
                'price'       => 'required|integer|min:0',
                'description' => 'nullable|string',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $validated['image_url'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($validated);

            return ApiResponse::success($product, 'Product created successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create product', 500, ['error' => $e->getMessage()]);
        }
    }
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name'        => 'sometimes|required|string|max:255',
                'price'       => 'sometimes|required|integer|min:0',
                'description' => 'nullable|string',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('image')) {
                // delete old image
                if ($product->image_url) {
                    Storage::disk('public')->delete($product->image_url);
                }

                $validated['image_url'] = $request->file('image')->store('products', 'public');
            }

            $product->update($validated);
            return ApiResponse::success($product, 'Product updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update product', 500, ['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return ApiResponse::error('Product not found', 404);
            }

            if ($product->image_url) {
                Storage::disk('public')->delete($product->image_url);
            }

            $product->delete();

            return ApiResponse::success(null, 'Product deleted successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete product', 500, ['error' => $e->getMessage()]);
        }
    }
}
