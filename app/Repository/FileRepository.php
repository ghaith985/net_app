<?php
namespace App\Repository;
use App\Models\EventType;
use App\Models\File;
use App\Models\FileEvent;
use App\Models\FileUserReserved;
use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileRepository implements  FileRepositoryInterface
{
    protected $fileModel;
    protected $userModel;
    protected $groupModel;
    protected $fileEventModel;
    protected $eventTypeModel;
    public  function __construct(File $fileModel,User $userModel,Group $groupModel,FileEvent $fileEventModel)
    {
        $this->fileModel=$fileModel;
        $this->userModel=$userModel;
        $this->groupModel=$groupModel;
        $this->fileEventModel=$fileEventModel;

    }
    public function uploadFileToGroup($data): ?File
    {
        $group = $this->groupModel->find($data['group_id']);
        if (!$group) {
            return null; // أو إرجاع رسالة خطأ مناسبة
        }

        $file = $data['file'];
        $fileName = $file->getClientOriginalName();
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $fileExtension = $file->getClientOriginalExtension();

        if (!$this->checkFileIfExist($data['group_id'], $fileNameWithoutExtension, $fileExtension)) {
            $exist = Storage::disk('public')->exists($fileName);

            if (!$exist) {
                // رفع الملف مباشرةً إلى storage/app/public/files
                $file->storeAs('', $fileName, 'public');

                $fileUrl = Storage::disk('public')->url($fileName);
                $fileRecord = new File(); // إنشاء كائن جديد
                $fileRecord->name = $fileNameWithoutExtension;
                $fileRecord->extension = $fileExtension;
                $fileRecord->group_id = $data['group_id'];
                $fileRecord->user_id = $data['user_id'];
                $fileRecord->is_active = true;
                $fileRecord->is_reserved = false;
                $fileRecord->path = $fileUrl;
                $fileRecord->save();
                return $fileRecord;
            } else {
                return null; // الملف موجود بالفعل
            }
        } else {
            return null; // الملف موجود في قاعدة البيانات
        }
    }


    public function checkFileIfExist($group_id,$file_name,$file_extension):bool
    {
        return $this->fileModel->where('group_id',$group_id)->where('name',$file_name)->where('extension',$file_extension)->where('is_active',1)->exists();
    }
    public function addFileEvent($file_id,$user_id)
    {
        $fileEventModel= new FileEvent();
        $fileEventModel->file_id=$file_id;
        $fileEventModel->user_id=$user_id;
        $fileEventModel->date=Carbon::now();

        $fileEventModel->save();
            return $fileEventModel;

    }
    public function downloadFileById($id)
        {
            // البحث عن الملف في قاعدة البيانات باستخدام الـ ID
            $file = File::find($id);
//             dd($file);
            // التحقق إذا لم يتم العثور على الملف
            if (!$file) {
                return response()->json(['error' => 'File not found.'], 404);
            }
            // تحديد مسار الملف بناءً على الاسم واللاحقة من قاعدة البيانات
            $filename = $file->name . '.' . $file->extension;
//            dd($filename);

            $path = 'public/files/' . $filename;
//            dd($path);

            // التحقق من وجود الملف في التخزين
            if (!Storage::exists($path)) {
                return response()->json(['error' => 'File not found in storage.'], 404);
            }
        return Storage::download($path, $filename);
            // إعادة الملف للتنزيل
//            return Storage::download($path);
        }
    public function deleteFile($data):bool
    {
        $result= $this->fileModel->where('id',$data['file_id'])->where('user_id',$data['user_id'])->update(['is_active'=>0]);
        $file=$this->fileModel->where('id',$data['file_id'])->where('user_id',$data['user_id'])->first();
        $path=$file->path;
        if ($file->group_id==1)
        {
            $path='public/'.$file->name.'.'.$file->extension;
            //dd($path);
        }
        $pathToTrash='trash/'.$file->name.'.'.$file->extension;
        // dd($pathToTrash);
        $isDone=Storage::move($path, $pathToTrash);
        // dd($isDone);

//        $f=$this->fileModel->find($data['file_id']);
//        $file=$data['file_id']->find();
        return $result;

    }

    public function checkIn($data):bool
    {
        $result= $this->fileModel->where('id',$data['file_id'])->where('is_active',1)->lockForUpdate()->update(['is_reserved'=>1]);
        return $result;
    }

    public function checkOut($data): bool
    {
        $result= $this->fileModel->where('id',$data['file_id'])->where('is_active',1)->update(['is_reserved'=>0]);
        return $result;
    }

    public function updateFileAfterCheckOut($data):?File
    {
        $file=$data['file'];
        $fileName=$file->getClientOriginalName();
//          dd($fileName);
        $basename = pathinfo($fileName, PATHINFO_FILENAME);
//         dd($basename);
        $fileExtension=$file->getClientOriginalExtension();
        // dd($fileExtension);
            $fileDb=$this->fileModel->where('id',$data['file_id'])->where('is_active',1)->first();
//       dd($fileDb);

        if($fileDb)
        {
            $groupName=$this->groupModel->where('id',$fileDb->group_id)->first()->name;
//         dd($groupName);
//            $exist=Storage::disk('public')->exists($fileName);
//             dd($exist);

                $result=Storage::disk('public')->put(  $fileName, file_get_contents($file), [

                    'overwrite' => true,
                ]);
//                dd($result);

                // dd($result);
                if($result)
                {

                    $fileUrl = Storage::disk('public')->url($fileName);
                    $fileDb->name = $basename;
                    $fileDb->extension = $fileExtension;
                    $fileDb->group_id = $fileDb['group_id'];
                    $fileDb->user_id = $fileDb['user_id'];
                    $fileDb->is_active = $fileDb['is_active'];
                    $fileDb->is_reserved = $fileDb['is_reserved'];
                    $fileDb->path = $fileUrl;
                    $fileDb->save();
                    return $fileDb;
//
                }
                else
                {
                    return null;
                }



        }else
        {
            return null;
        }
    }

    public function bulkCheckIn($data): bool
    {
        $count=count($data);
        $isReserved=false;
        for($i=1;$i<=$count-1;$i++)
        {
            $id=$data['id'.$i];
            $result= $this->fileModel->where('id',$id)->where('is_active',1)->lockForUpdate()->update(['is_reserved'=>1]);
            if ($result)
                $isReserved=true;
            else
                $isReserved=false;
        }
        return $isReserved;
    }
    public function backupFile(int $fileId, string $event): bool
    {
        $file = $this->fileModel->find($fileId);

        if (!$file) {
            throw new \Exception("File not found");
        }

        return DB::table('file_backups')->insert([
            'file_id' => $fileId,
            'file_data' => json_encode($file),
            'version' => now()->format('YmdHis') . "_$event",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


}
