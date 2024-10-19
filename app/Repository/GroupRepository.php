<?php

namespace App\Repository;

use App\Models\File;
use App\Models\FileUserReserved;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroupRepository implements GroupRepositoryInterface
{
    public function createGroup(array $data): Group
    {


        $group = new Group();
        $group->name = $data['name'];
        $group->owner_id = $data['owner_id'];
        $group->save();

        $groupMember = new GroupMember();
        $groupMember->group_id = $group->id;
        $groupMember->user_id = $data['owner_id'];
        $groupMember->join_date = Carbon::now();
        $groupMember->save();

        $returnGroup = new Group();
        $returnGroup->id = $group->id;
        $returnGroup->name = $group->name;
        $returnGroup->owner_id = $group->owner_id;
        $returnGroup->updated_at = $group->updated_at;
        $returnGroup->created_at = $group->created_at;

        return $returnGroup;
    }
    public function deleteGroup(array $data)
    {
        $groupOwner = Group::where('id', $data['group_id'])
            ->where('owner_id', auth()->id());

        if ($groupOwner->count() > 0)
//        if(isset($groupOwner))
        {
            $groupOwner->delete();
            return  true;
        }
        return false;
    }
    public function groupUsers($data)
    {
        $groupuser = GroupMember::whereIn('group_id',$data['group_id'])->with('user')->get();
        return $groupuser;
    }
    public function allUserGroup()
    {
        $userId = auth()->id();
        $userGroups = GroupMember::where('user_id', $userId)->with('group')->get();
        return $userGroups;
    }

    public function addUserToGroup($data)
    {
        $currentUserId = Auth::id();
        $group = Group::find($data->group_id);
        if (!$group) {
            return response()->json([
                'messages'=>'Group not found',
            ]);
        }
        if ($group->owner_id !== $currentUserId) {
            return response()->json([
                'messages'=>'Dont have access to add to Group',
            ],400);
        }
        $existingMember = GroupMember::where('group_id', $data->group_id)->where('user_id', $data->user_id)->first();

        if ($existingMember) {
            return response()->json([
                'messages'=>'User has in Group Already',
            ],405);
        }
        $newMember = new GroupMember();
        $newMember->group_id = $data->group_id;
        $newMember->user_id = $data->user_id;
        $newMember->join_date = now(); // يمكنك تغيير التاريخ حسب الحاجة
        $newMember->save();
        return response()->json([
            'messages'=>'User Added To Group',
        ],201);
    }





    public function deleteUserFromGroup($data){
        $currentUserId = Auth::id();
        $group = Group::find($data->group_id);

        if ($group) {
            return response()->json([
                'messages'=>'Group not found',
            ],404);
        }
        if ($group->owner_id !== $currentUserId) {
            return response()->json([
                'messages'=>'Dont have access to delete from Group',
            ]);
        }
        $file_user_reserve = FileUserReserved::where('group_id', $data->group_id)->where('user_id', $data->user_id)->first();
        if ($file_user_reserve){
            return response()->json([
                'messages'=>'User has Reserved File',
            ],405);
        }


        $existingMember = GroupMember::where('group_id', $data->group_id)->where('user_id', $data->user_id)->first();

        if (!$existingMember) {
            return response()->json([
                'messages'=>'User not in Group',
            ],405);
        }
        else{
            GroupMember::where('group_id', $data->group_id)->where('user_id', $data->user_id)->delete();
            return response()->json([
                'messages'=>'User Deleted Successfully',
            ],405);
        }
    }





}
