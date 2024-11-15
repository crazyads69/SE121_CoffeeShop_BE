<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class UserService
{
    use ResponseTrait;

    public function getListUser(): JsonResponse
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return $this->successResponse($users, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function getUserByInfo($id = null, $name = null, $email = null): JsonResponse
    {
        if($id != null) {
            $user = User::find($id);
        } elseif($name != null) {
            $user = User::where('name', $name)->first();
        } elseif($email != null) {
            $user = User::where('email', $email)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, tên hoặc số của người dùng');
        }

        if(!$user) {
            return $this->errorResponse('Không tìm thấy người dùng');
        }

        return $this->successResponse($user, 'Lấy Dữ Liệu Thành Công !!!');
    }

    public function deleteUser($id = null, $name = null, $email = null): JsonResponse
    {
        if($id != null) {
            $user = User::find($id);
        } elseif($name != null) {
            $user = User::where('name', $name)->first();
        } elseif($email != null) {
            $user = User::where('email', $email)->first();
        } else {
            return $this->errorResponse('Không nhận diện được id, tên hoặc số của người dùng');
        }

        if(!$user) {
            return $this->errorResponse('Không tìm thấy người dùng');
        }

        $user->delete();
        return $this->successResponse([], 'Xóa Người Dùng Thành Công !!!');
    }
}
