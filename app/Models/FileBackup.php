<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'file_id',
        'file_data',
        'version',
    ];

    /**
     * Define the relationship to the File model.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
