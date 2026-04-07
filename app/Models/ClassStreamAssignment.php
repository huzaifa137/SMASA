<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassStreamAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'class_id',
        'stream_id',
    ];


    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'class_id', 'class_id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }
}