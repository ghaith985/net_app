<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function fileEvents()
    {
        return $this->hasMany(FileEvent::class);
    }
}
