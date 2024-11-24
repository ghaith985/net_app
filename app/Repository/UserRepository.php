<?php
namespace App\Repository;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function register(array $data): User
    {
        if ($data['role_id'] == 1) {
            // تحقق من وجود أدمن آخر
            $existingAdmin = User::where('role_id', 1)->first();

            if ($existingAdmin) {
                // إرجاع رسالة خطأ
                throw new \Exception('An admin already exists. You cannot create more than one admin.');
            }
        }

        $user = new User();
        $user->name=$data['name'];
        $user->email=$data['email'];
        $user->password=bcrypt($data['password']);
        $user->first_name=$data['first_name'];
        $user->last_name=$data['last_name'];
        $user->role_id=$data['role_id'];
        // تحديد قيمة is_admin بناءً على role_id
        $user->is_admin = $data['role_id'] == 1 ? true : false;
        $user->save();

        return $user;
    }
    public function displayAllUser(){
        $allUser = User::all();
        return response()->json([
            'data'=>$allUser
        ],200);
    }
}
