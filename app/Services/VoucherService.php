<?php

namespace App\Services;

use App\Models\Voucher;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class VoucherService
{
    use ResponseTrait;

    public static function verifyVoucher(string $voucherCode)
    {
        $voucher = Voucher::where('voucher_code', $voucherCode)->first();

        if ($voucher == null) {
            $data = [
                'isAvailable' => false,
                'voucherType' => null,
                'voucherAmount' => null,
                'quantity' => null,
                'voucher' => null,
            ];

            return $data;
        }

        $voucherType = $voucher['type'];
        $voucherAmount = $voucher['amount'];
        $quantity = $voucher['quantity'];
        $startDate = $voucher['start_date'];
        $endDate = $voucher['end_date'];

        $data = [
            'isAvailable' => true,
            'voucherType' => $voucherType,
            'voucherAmount' => $voucherAmount,
            'quantity' => $quantity,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'voucher' => $voucher,
        ];

        return $data;
    }

    public function getListVoucher(): JsonResponse
    {
        $vouchers = Voucher::orderBy('created_at', 'desc')->get();
        return $this->successResponse($vouchers, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function getVoucherByInfo($id = null, $code = null): JsonResponse
    {
        if($id != null) {
            $voucher = Voucher::find($id);
        } elseif($code != null) {
            $voucher = Voucher::where('voucher_code', $code)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, code của mã giảm giá');
        }

        if(!$voucher) {
            return $this->errorResponse('Không tìm thấy mã giảm giá');
        }

        return $this->successResponse($voucher, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function deleteVoucher($id = null, $code = null): JsonResponse
    {
        if($id != null) {
            $voucher = Voucher::find($id);
        } elseif($code != null) {
            $voucher = Voucher::where('voucher_code', $code)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, code mã giảm giá');
        }

        if(!$voucher) {
            return $this->errorResponse('Không tìm thấy mã giảm giá');
        }

        $voucher->delete();
        return $this->successResponse([], 'Xóa Mã Giảm Giá Thành Công !!!');
    }
}
