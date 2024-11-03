<?php

namespace App\Services;

use App\Http\Controllers\Api\StaffController;
use App\Models\Loyal;
use App\Models\User;

class HandleResponseTask
{
    public static function handleTask($response)
    {
        if ($response == 'GET_EMPLOYEE_LIST') {
            $staffs = User::all()->where('role', 0);
            $staffsList = [];
            foreach ($staffs as $staff) {
                $staffsList[] = [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'created_at' => $staff->created_at,
                ];
            }
            return response()->json()->setData(['task' => 'GET_EMPLOYEE_LIST', 'data' => $staffsList]);
        } else if ($response == 'GET_LOYAL_LIST') {
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
        } else {
            return response()->json()->setData(['task' => 'UNKNOWN']);
        }

        return response()->json("Hello, I'm ChatBot");
    }
}
