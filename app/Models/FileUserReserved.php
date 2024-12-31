<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUserReserved extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'file_id', // أضف هذا الحقل
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
