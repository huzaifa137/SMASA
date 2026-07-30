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
        'custom_subject_id',
        'subject_source',
        'subject_type',
        'school_id',
        'subject_teacher_1',
        'subject_teacher_2',
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

    /**
     * A school's own subject, only populated when subject_source = 'custom'.
     */
    public function customSubject()
    {
        return $this->belongsTo(CustomSubject::class, 'custom_subject_id');
    }

    /**
     * Safe display name regardless of whether this row points at the shared
     * master subject list (legacy schools) or a school's own custom subject.
     * Callers that used to read master-data names directly can switch to
     * this accessor without needing to know which mode a school is in.
     */
    public function getDisplayNameAttribute()
    {
        if ($this->subject_source === 'custom' && $this->customSubject) {
            return $this->customSubject->subject_name;
        }

        return \App\Http\Controllers\Helper::recordMdname($this->subject_id);
    }
}