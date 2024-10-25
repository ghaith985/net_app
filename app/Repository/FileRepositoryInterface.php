<?php
namespace App\Repository;
use App\Models\EventType;
use App\Models\File;
use App\Models\FileEvent;

interface FileRepositoryInterface
{
    public function uploadFileToGroup($data): ?File;
    public function checkFileIfExist($group_id, $file_name, $file_extension): bool;

    public function addFileEvent($file_id, $user_id);



}
