<?php

namespace App\Services;

use App\Http\Controllers\Api\StaffController;
use App\Models\Loyal;
use App\Models\User;
use App\Models\Voucher;

class HandleResponseTask
{
    public static function handleTask($userMessage, $response)
    {
        if (str_contains($response, 'GET_EMPLOYEE_LIST')) {
            $staffs = User::all()->where('role', 0);
            $staffsName = '';
            foreach ($staffs as $staff) {
                $staffsName = $staffsName . $staff->id . ') '. $staff->name . ', ';
            }
            $staffsName = rtrim($staffsName, ', ');

            $messages = 'Đây là danh sách nhân viên của cửa hàng: ' . $staffsName;
            return response()->json()->setData(['task' => 'GET_EMPLOYEE_LIST', 'data' => $messages]);
        } else if (str_contains($response, 'REMOVE_EMPLOYEE_OUT_OF_LIST')) {
            // Filter the Numbers from String
            $int_var = preg_replace('/[^0-9]/', '', $userMessage);

            if ($int_var == '') {
                return response()->json()->setData(['task' => 'REMOVE_EMPLOYEE_OUT_OF_LIST', 'data' => 'Không tìm thấy nhân viên cần xóa']);
            } else {
                $staff = User::where('id', $int_var)->first();
                // TODO: Implement delete
                return response()->json()->setData(['task' => 'REMOVE_EMPLOYEE_OUT_OF_LIST', 'data' => $staff]);
            }
        } else if ($response == 'GET_VOUCHER_LIST') {
            $vouchers = Voucher::all();
            /*
             * $staffsName = '';
            foreach ($staffs as $staff) {
                $staffsName = $staffsName . $staff->id . ') '. $staff->name . ', ';
            }
            $staffsName = rtrim($staffsName, ', ');
             */
            $vouchersTitle = '';
            foreach ($vouchers as $voucher) {
                $vouchersTitle = $vouchersTitle . $voucher->id . ') '. $voucher->title . ', ';
            }

            $vouchersTitle = rtrim($vouchersTitle, ', ');
            $messages = 'Đây là danh sách voucher hiện tại của cửa hàng: ' . $vouchersTitle;
            return response()->json()->setData(['task' => 'GET_VOUCHER_LIST', 'data' => $messages]);
        }
        else if ($response == 'GET_LOYAL_LIST') {
            $loyals = Loyal::all();
            $loyalsList = [];
            foreach ($loyals as $loyal) {
                $loyalsList[] = [
                    'id' => $loyal->id,
                    'name' => $loyal->name,
                    'email' => $loyal->email,
                    'created_at' => $loyal->created_at,
                ];
            }
            return response()->json()->setData(['task' => 'GET_LOYAL_LIST', 'data' => $loyalsList]);
        } else if ($response == 'GET_CUSTOMER_LIST') {
            $customers = User::all()->where('role', 1);
            $customersList = [];
            foreach ($customers as $customer) {
                $customersList[] = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'created_at' => $customer->created_at,
                ];
            }
            return response()->json()->setData(['task' => 'GET_CUSTOMER_LIST', 'data' => $customersList]);
        }
        else {
            return response()->json()->setData(['task' => $response]);
        }
    }
}
