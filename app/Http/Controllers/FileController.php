<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileUserReserved;
use App\Repository\FileRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    public function downloadFile(Request $request)
    {
        $data = $request->all();
        $rules = [
            'file_id' => 'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 400);
        }
        $user_id = auth()->user()->id;
        $data['user_id'] = $user_id;
        DB::beginTransaction();
        try {
            $response = $this->fileRepository->downloadFileById($data['file_id']);

            // هنا نتحقق إذا كانت الاستجابة كائن StreamedResponse
            if ($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                $fileEvent = $this->fileRepository->addFileEvent($data['file_id'], $user_id, 2);
                if ($fileEvent) {
                    DB::commit();
                    return $response; // أعد كائن StreamedResponse مباشرة
                } else {
                    DB::rollback();
                    return response()->json(['status' => false, 'message' => 'File Events not Complete'], 500);
                }
            } else {
                DB::rollback();
                return response()->json(['status' => false, 'message' => 'Error downloading file.'], 500);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function deleteFile(Request $request)
    {

        $data=$request->all();
        $rules=[
            'file_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }

        $user_id=auth()->user()->id;
        $data['user_id']=$user_id;



        DB::beginTransaction();
        try {
            $responseData=$this->fileRepository->deleteFile($data);
//dd($responseData);

            if ($responseData)
            {
                $fileEvent=$this->fileRepository->addFileEvent($data['file_id'],$user_id);
//                dd($fileEvent);
                if ($fileEvent)
                {
                    $file_id=$data['file_id'];
                    File::find($file_id)->delete();
                    DB::commit();
                    return response()->json(['status'=>true,'message'=>'File Deleted Successfully'],200);
                }
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File not Deleted'],500);
            }
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }


    public function checkIn(Request $request):JsonResponse
    {
        $data=$request->all();
        $rules=[
            'file_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;

        DB::beginTransaction();
        try{
            $this->fileRepository->backupFile($data['file_id'], 'before_checkin');
            // تنفيذ عملية الحجز
            $checkin=$this->fileRepository->checkIn($data);
            if($checkin)
            {
                $fileEvent=$this->fileRepository->addFileEvent($data['file_id'],$user_id,4);
//            dd($fileEvent);
                if ($fileEvent)
                {
                    $file_id=$data['file_id'];
                    $file=File::find($file_id);
                    $file_user_reserved = new FileUserReserved();
                    $file_user_reserved->group_id = $file->group_id;
                    $file_user_reserved->user_id = $file->user_id;
                    $file_user_reserved->save();
                    // نسخ احتياطي بعد الحجز
                    $this->fileRepository->backupFile($data['file_id'], 'after_checkin');

                    DB::commit();
                    return response()->json(['status'=>true,'message'=>'File Has Been Reserved'],200);
                }
                else
                {
                    return  response()->json(['status'=>false,'message'=>'Event File not Complete!'],500);

                }
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File Not Reserved'],500);

            }
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function checkOut(Request $request):JsonResponse
    {
        $data=$request->all();
        $rules=[
            'file_id'=>'required|integer'
        ];
        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }
        $user_id=auth()->user()->id;

        DB::beginTransaction();
        try {
            // نسخ احتياطي قبل إلغاء الحجز
            $this->fileRepository->backupFile($data['file_id'], 'before_checkout');
            // تنفيذ عملية إلغاء الحجز
            $checkout=$this->fileRepository->checkOut($data);
            if($checkout)
            {
                $fileEvent=$this->fileRepository->addFileEvent($data['file_id'],$user_id,5);
                if ($fileEvent)
                {
                    $file_id=$data['file_id'];
                    $file=File::find($file_id);
                    FileUserReserved::where('group_id', $file->group_id)->where('user_id', $file->user_id)->delete();
                    // نسخ احتياطي بعد إلغاء الحجز
                    $this->fileRepository->backupFile($data['file_id'], 'after_checkout');
                    DB::commit();
                    return response()->json(['status'=>true,'message'=>'File Has Been Un-Reserved'],200);
                }
                else
                {
                    return  response()->json(['status'=>false,'message'=>'Event File not Complete!'],500);

                }
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File Not Un-Reserved'],500);

            }
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function updateFileAfterCheckOut(Request $request)
    {
        $data=$request->all();
        $rules=[
            'file'=>'required',
            'file_id'=>'required|integer'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails())
        {
            return response()->json(['status'=>false,'message'=>$validation->errors()->first()],500);
        }

        DB::beginTransaction();
        try {
            $file=$this->fileRepository->updateFileAfterCheckOut($data);
            if ($file)
            {
                $fileEvent=$this->fileRepository->addFileEvent($file->id,auth()->user()->id,6);
                if($fileEvent)
                {
                    DB::commit();
                    return response()->json(['status'=>true,'message'=>'File updated successfully'],200);
                }
                else
                {
                    return response()->json(['status'=>false,'message'=>'File Events not Complete '],500);
                }
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'File update failed'],500);
            }
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function bulkCheckIn(Request $request):JsonResponse
    {
        $data=$request->all();
        $result=$this->fileRepository->bulkCheckIn($data);
        DB::beginTransaction();
        try{
            if ($result)
            {
                $file_id=$data['id1'];
                $file=File::find($file_id);
                $file_user_reserved = new FileUserReserved();
                $file_user_reserved->group_id = $file->group_id;
                $file_user_reserved->user_id = $file->user_id;
                $file_user_reserved->save();

                $file_id=$data['id2'];
                $file=File::find($file_id);
                $file_user_reserved = new FileUserReserved();
                $file_user_reserved->group_id = $file->group_id;
                $file_user_reserved->user_id = $file->user_id;
                $file_user_reserved->save();

                DB::commit();

                return response()->json(['status'=>true,'message'=>'Files Has Been Checked In'],200);
            }
            else
            {
                return response()->json(['status'=>false,'message'=>'Files Not Checked In'],500);
            }
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }
    public function backupFile(Request $request, int $fileId): JsonResponse
    {
        try {
            // اجلب الحدث من الطلب، مع إعطاء قيمة افتراضية إذا لم يتم توفيرها
            $event = $request->input('event', 'manual');

            // استدعاء التابع في Repository مع تمرير fileId وevent
            $backupStatus = $this->fileRepository->backupFile($fileId, $event);

            if ($backupStatus) {
                return response()->json(['status' => true, 'message' => 'Backup created successfully']);
            }

            return response()->json(['status' => false, 'message' => 'Failed to create backup'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    }
