<?php
namespace App\Repository;

use App\Models\Group;
use http\Env\Request;

interface GroupRepositoryInterface
{
    public function createGroup(array $data):Group;
    public function deleteGroup(array $data);
    public function groupUsers($data);
    public function allUserGroup();
    public function addUserToGroup($request);
    public function deleteUserFromGroup($data);
//    public function userDeleteFromGroup($request);


}
