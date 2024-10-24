<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'owner_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }
    public function fileUserReserved()
    {
        return $this->hasMany(FileUserReserved::class);
    }
    public function requestUserToGroups()
    {
        return $this->hasMany(RequestUserToGroups::class);
    }
    public function files()
    {
        return $this->hasMany(File::class);
    }
}
