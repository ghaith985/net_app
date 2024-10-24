<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'file_id',
        'event_type_id',
        'user_id',
        'date',
        'details',
    ];
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}