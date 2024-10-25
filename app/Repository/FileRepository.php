<?php
namespace App\Repository;
use App\Models\EventType;
use App\Models\File;
use App\Models\FileEvent;
use App\Models\FileUserReserved;
use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;
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

            $exist = Storage::disk('local')->exists($group->name . '/' . $fileName);

            if (!$exist) {

                // استخدام move بدلاً من file_get_contents
                $file->storeAs($group->name, $fileName);

                $fileUrl = Storage::disk('local')->url($group->name . '/' . $fileName);
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


            }
            else {
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
}
