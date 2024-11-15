<?php

namespace App\Services;

use App\Models\Loyal;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class LoyalService
{
    use ResponseTrait;

    public static function applyLoyal(float $totalPrice, Loyal $loyal)
    {
        if ($loyal->type == 'direct') {
            $discountPrice = $loyal->amount;

            $finalPrice = $totalPrice - $discountPrice;

            return [$discountPrice, $finalPrice];
        }

        $discountPrice = $totalPrice * $loyal->amount / 100;

        $finalPrice = $totalPrice - $discountPrice;

        return [$discountPrice, $finalPrice];
    }

    public static function getCurrentLoyal(float $totalSpent) {
        $loyals = Loyal::all();
        $currentLoyal = new Loyal();

        foreach ($loyals as $loyal) {
            if ($totalSpent >= $loyal->spending_min && $totalSpent <= $loyal->spending_max) {
                $currentLoyal = $loyal;
            }
        }

        return $currentLoyal;
    }

    public function getListLoyal(): JsonResponse
    {
        $loyals = Loyal::orderBy('created_at', 'desc')->get();
        return $this->successResponse($loyals, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function getLoyalByInfo($id = null, $name = null): JsonResponse
    {
        if($id != null) {
            $loyal = Loyal::find($id);
        } elseif($name != null) {
            $loyal = Loyal::where('name', $name)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, tên của khách hàng trung thành');
        }

        if(!$loyal) {
            return $this->errorResponse('Không tìm thấy khách hàng trung thành');
        }

        return $this->successResponse($loyal, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function deleteLoyal($id = null, $name = null): JsonResponse
    {
        if($id != null) {
            $user = Loyal::find($id);
        } elseif($name != null) {
            $user = Loyal::where('name', $name)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, tên của khách hàng trung thành');
        }

        if(!$user) {
            return $this->errorResponse('Không tìm thấy khách hàng trung thành');
        }

        $user->delete();
        return $this->successResponse([], 'Xóa Khách Hàng Thành Công !!!');
    }
}
