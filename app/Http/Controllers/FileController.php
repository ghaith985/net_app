<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Repository\FileRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{

    protected $fileRepository;
    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository=$fileRepository;
    }
    public function uploadFileToGroup(Request $request):JsonResponse
    {
        $data=$request->all();
        $rules=[
            'file'=>'required|max:5024',
            'group_id'=>'required|integer'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;
        $data['user_id']=$user_id;

        $file=$this->fileRepository->uploadFileToGroup($data);
        if ($file)
        {

            $fileEvent=$this->fileRepository->addFileEvent($file->id, $user_id);
            if($fileEvent)
            {
                return response()->json(['status'=>true,'message'=>'File uploaded successfully',],200);
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File Events not Complete '],500);
            }
        }
        else
        {
            return response()->json(['status'=>false,'message'=>'File upload failed'],500);
        }
    }


}
