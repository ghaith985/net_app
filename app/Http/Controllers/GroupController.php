<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Repository\GroupRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    protected $groupRepository;
    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }
    public function creatGroup(Request $request):JsonResponse
    {
        $data = $request->all();
        $rules = [
            'name' => 'required|regex:/^[a-zA-Z0-9]+$/'
        ];
        $owner_id = auth()->user()->id;
        $data['owner_id'] = $owner_id;
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
//            $errors = $validation->errors();
            return response()->json([
                "messages" => $validation->errors()
            ], 422);
        }
        $group = $this->groupRepository->createGroup($data);
        if ($group) {
            return response()->json([
                'messages'=>'Group Created Successfully',
                'data'=>$group
            ],201);
        } else
        {
            return response()->json([
                'messages' => 'Group Not Created',
            ],406);
        }
    }
    public function deleteGroup(Request $request):JsonResponse
    {
        $data=$request->all();
//        dd($this->groupRepository->deleteGroup($data));
        $isTrue=$this->groupRepository->deleteGroup($data);
//        dd($isTrue);
        if($isTrue) {
            return response()->json([
                'messages'=>'Group Deleted Successfully',
            ],200);
        }
        else {
            return response()->json([
                'messages'=>'Not Owned Group',
            ],400);
        }

    }
    public function groupUsers(Request $request):JsonResponse
    {
        $data=$request->all();
        $groupuser = $this->groupRepository->GroupUsers($data);
        return response()->json([
            'messages'=>'Successfully',
            'data'=>$groupuser
        ],200);
    }

    public function allUserGroup(Request $request):JsonResponse
    {
        $data=$request->all();
        $allUserGroup = $this->groupRepository->allUserGroup();
        return response()->json([
            'messages'=>'Successfully',
            'data'=>$allUserGroup
        ],200);
    }
    public function addUserToGroup(Request $request)
    {
        return $this->groupRepository->addUserToGroup($request);
    }
    public function deleteUserFromGroup(Request $request)
    {
        return $this->groupRepository->deleteUserFromGroup($request);
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        //
    }
}
