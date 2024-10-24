<?php
namespace App\Repository;
use App\Models\User;
interface UserRepositoryInterface
{
    public function register(array $data): User;
    public function displayallUser();

}

