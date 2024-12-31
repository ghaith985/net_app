<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class GroupMember extends Model
{
    use HasFactory;
    use Notifiable;
    protected $fillable = ['group_id', 'user_id', 'join_date'];
    protected $searchableFields = ['*'];

    protected $table = 'group_members';
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
