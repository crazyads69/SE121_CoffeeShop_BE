<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class CustomerService
{
    use ResponseTrait;

    public static function createCustomer(string $phoneNumber) {
        $customer = new Customer();
        $customer->phone_number = $phoneNumber;
        $customer->save();

        return $customer;
    }
    public static function getCurrentLoyal(string $customerPhoneNumber) {
        $totalSpent = self::getTotalSpent($customerPhoneNumber);

        $currentLoyal = LoyalService::getCurrentLoyal($totalSpent);

        return $currentLoyal;
    }

    public static function getTotalSpent(string $customerPhoneNumber) {
        $customer = Customer::where('phone_number', $customerPhoneNumber)->first();

        if (!$customer) {
            return 0;
        }

        $invoices = $customer->invoices;
        $totalSpent = 0;

        if (empty($invoices)) {
            return 0;
        }

        foreach ($invoices as $invoice) {
            $totalSpent += $invoice->final_price;
        }

        return $totalSpent;
    }

//    Chatbot
    public function getListCustomer(): JsonResponse
    {
        $customers = Customer::orderBy('created_at', 'desc')->get();
        return $this->successResponse($customers, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function getCustomerByInfo($id = null, $name = null, $phone = null): JsonResponse
    {
        if($id != null) {
            $customer = Customer::find($id);
        } elseif($name != null) {
            $customer = Customer::where('name', $name)->first();
        } elseif($phone != null) {
            $customer = Customer::where('phone_number', $phone)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, tên hoặc số của khách hàng');
        }

        if(!$customer) {
            return $this->errorResponse('Không tìm thấy khách hàng');
        }

        return $this->successResponse($customer, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function deleteCustomer($id = null, $name = null, $phone = null): JsonResponse
    {
        if($id != null) {
            $customer = Customer::find($id);
        } elseif($name != null) {
            $customer = Customer::where('name', $name)->first();
        } elseif($phone != null) {
            $customer = Customer::where('phone_number', $phone)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, tên hoặc số của khách hàng');
        }

        if(!$customer) {
            return $this->errorResponse('Không tìm thấy khách hàng');
        }

        $customer->delete();
        return $this->successResponse([], 'Xóa Khách Hàng Thành Công !!!');
    }
}
