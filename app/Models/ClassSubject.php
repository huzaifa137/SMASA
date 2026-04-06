<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'stream_id',
        'subject_id',
        'subject_type',
        'school_id',
    ];



// Replace this with a custom method that fetches subjects by class_id + stream_id
public function classSubjectsByClassAndStream()
{
    return ClassSubject::where('class_id', $this->class_id)
                       ->where('stream_id', $this->stream_id)
                       ->where('school_id', $this->school_id);
}

    // /**
    //  * Get the actual subject details (assuming you have a 'Subject' model).
    //  */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id'); // Assuming 'Subject' is your subject model
    }
}