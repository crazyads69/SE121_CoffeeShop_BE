<?php

namespace App\Services;

use App\Models\BankConfig;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class BankConfigService
{
    use ResponseTrait;

    public function getListBankConfig(): JsonResponse
    {
        $bankConfigs = BankConfig::orderBy('created_at', 'desc')->get();
        return $this->successResponse($bankConfigs, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function getBankConfigByInfo($id = null,$bank_id = null, $bank_number = null, $bank_account_name = null): JsonResponse
    {
        if($id != null) {
            $bankConfig = BankConfig::find($id);
        } elseif($bank_id != null) {
            $bankConfig = BankConfig::where('bank_id', $bank_id)->first();
        } elseif($bank_number != null) {
            $bankConfig = BankConfig::where('bank_number', $bank_number)->first();
        } elseif($bank_account_name != null) {
            $bankConfig = BankConfig::where('bank_account_name', $bank_account_name)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, bank_id, bank_number hoặc bank_account_name của ngân hàng');
        }

        if(!$bankConfig) {
            return $this->errorResponse('Không tìm thấy ngân hàng');
        }

        return $this->successResponse($bankConfig, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function deleteBankConfig($id = null,$bank_id = null, $bank_number = null, $bank_account_name = null): JsonResponse
    {
        if($id != null) {
            $bankConfig = BankConfig::find($id);
        } elseif($bank_id != null) {
            $bankConfig = BankConfig::where('bank_id', $bank_id)->first();
        } elseif($bank_number != null) {
            $bankConfig = BankConfig::where('bank_number', $bank_number)->first();
        } elseif($bank_account_name != null) {
            $bankConfig = BankConfig::where('bank_account_name', $bank_account_name)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, bank_id, bank_number hoặc bank_account_name của ngân hàng');
        }

        if(!$bankConfig) {
            return $this->errorResponse('Không tìm thấy ngân hàng');
        }

        $bankConfig->delete();
        return $this->successResponse([], 'Xóa Cấu Hình Ngân Hàng Thành Công !!!');
    }
}
