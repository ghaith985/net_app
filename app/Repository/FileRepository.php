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
    public  function __construct(File $fileModel,User $userModel,Group $groupModel,FileEvent $fileEventModel,EventType $eventTypeModel)
    {
        $this->fileModel=$fileModel;
        $this->userModel=$userModel;
        $this->groupModel=$groupModel;
        $this->fileEventModel=$fileEventModel;
        $this->eventTypeModel=$eventTypeModel;

    }
    public function uploadFileToGroup($data):?File
    {
        $groupName=$this->groupModel->where('id',$data['group_id'])->first()->name;
        $file=$data['file'];
        $fileName=$file->getClientOriginalName();
        $basename = pathinfo($fileName, PATHINFO_FILENAME);

        $fileNameWithoutExtension=pathinfo($fileName, PATHINFO_FILENAME);

        $fileExtension=$file->getClientOriginalExtension();
        if (!$this->checkFileIfExist($data['group_id'],$fileNameWithoutExtension,$fileExtension))
        {
            $exist=Storage::disk('local')->exists($groupName.'/'.$fileName);
            if(!$exist) {
                //Store File in Local Disk in the folder with group name
                Storage::disk('local')->put($groupName . '/' . $fileName, file_get_contents($file), [
                    'overwrite' => false,
                ]);
                $fileUrl = Storage::disk('local')->url($groupName . '/' . $fileName);
                $this->fileModel->name = $fileNameWithoutExtension;
                $this->fileModel->extension = $fileExtension;
                $this->fileModel->group_id = $data['group_id'];
                $this->fileModel->user_id = $data['user_id'];
                $this->fileModel->is_active = true;
                $this->fileModel->is_reserved = false;
                $this->fileModel->path = $fileUrl;
                $this->fileModel->save();
                return $this->fileModel;
            }else
                return null;

        }
        else
        {
            return null;
        }

    }
    public function checkFileIfExist($group_id,$file_name,$file_extension):bool
    {
        return $this->fileModel->where('group_id',$group_id)->where('name',$file_name)->where('extension',$file_extension)->where('is_active',1)->exists();
    }
    public function addFileEvent($file_id,$user_id,$event_type_id)
    {
        $fileEventModel= new FileEvent();
        $fileEventModel->file_id=$file_id;
        $fileEventModel->event_type_id=$event_type_id;
        $fileEventModel->user_id=$user_id;
        $fileEventModel->date=Carbon::now();
        $fileEventModel->save();
        if ($fileEventModel)
            return $fileEventModel;
        else
            return null;


    }
}
