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
    public function displayAllGroups();
    public function searchUser($data);
    public function searchGroup($data);
    public function RequestToJoinGroup($data);
    public function AcceptedRequest($data);
    public function unAcceptedRequest($data);
    public function displayUserRequestForGroup($data);






}
