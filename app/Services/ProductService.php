<?php

namespace App\Services;

use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class ProductService
{
    use ResponseTrait;

    public function getListProduct($category = null): JsonResponse
    {
        if(!$category) {
            $products = Product::orderBy('created_at', 'desc')->get();
        } else {
            $products = Product::where('type', $category)->orderBy('created_at', 'desc')->get();
        }
        return $this->successResponse($products, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function getProductByInfo($id = null, $name = null): JsonResponse
    {
        if($id != null) {
            $product = Product::find($id);
        } elseif($name != null) {
            $product = Product::where('name', $name)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id hoặc tên của sản phẩm');
        }

        if(!$product) {
            return $this->errorResponse('Không tìm thấy sản phẩm');
        }

        return $this->successResponse($product, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function deleteProduct($id = null, $name = null): JsonResponse
    {
        if($id != null) {
            $product = Product::find($id);
        } elseif($name != null) {
            $product = Product::where('name', $name)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id hoặc tên của sản phẩm');
        }

        if(!$product) {
            return $this->errorResponse('Không tìm thấy sản phẩm');
        }

        $product->delete();
        return $this->successResponse([], 'Xóa Sản Phẩm Thành Công !!!');
    }
}
