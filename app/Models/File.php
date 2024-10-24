<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'extension',
        'group_id',
        'user_id',
        'path',
        'is_active',
        'is_reserved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    public function fileEvents()
    {
        return $this->hasMany(FileEvent::class);
    }
}
