<?php

namespace App\Services;

use App\Http\Controllers\Api\StaffController;
use App\Models\Loyal;
use App\Models\User;

class StaffService
{
    public static function getList()
    {
        return User::where('role', 0);
    }
}
