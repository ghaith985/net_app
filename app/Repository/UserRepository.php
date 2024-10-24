<?php
namespace App\Repository;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function register(array $data): User
    {
        $user = new User();
        $user->name=$data['name'];
        $user->email=$data['email'];
        $user->password=bcrypt($data['password']);
        $user->first_name=$data['first_name'];
        $user->last_name=$data['last_name'];
        $user->role_id=$data['role_id'];
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
