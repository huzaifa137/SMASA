<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
  protected $fillable = [
    'firstname',
    'lastname',
    'senior',
    'stream',
    'gender',
    'school_id',
    'linked_student_id',
    'admission_number',
    'primary_contact',
    'other_contact',
    'student_photo',
    'date_of_admission',
    'ple_score',
    'uce_score',
    'previous_school',
    'primary_school_name',
    'guardian_names',
    'relation',
    'guardian_phone',
    'guardian_email',
    'home_address',
    'date_of_birth',
    'place_of_birth',
    'birth_certificate_entry_number',
    'nationality',
    'medical_history',
    'comments',
    'added_by',
  ];

  // public function school()
  // {
  //   return $this->belongsTo(School::class);
  // }

  /**
   * ── Student consolidation ────────────────────────────────────────────
   * A student may have more than one enrollment row (e.g. one Theology
   * class row + one Secular class row) representing the same physical
   * child. The PRIMARY row has `linked_student_id = null`. Every other
   * row that belongs to the same child points at the primary row's id.
   */

  // The primary/master record this row was consolidated into (null if this IS the primary).
  public function primaryRecord()
  {
    return $this->belongsTo(Student::class, 'linked_student_id');
  }

  // Other enrollment rows (other programs) consolidated under this record.
  public function linkedRecords()
  {
    return $this->hasMany(Student::class, 'linked_student_id');
  }

  public function isConsolidatedChild(): bool
  {
    return !is_null($this->linked_student_id);
  }

  public function isConsolidatedPrimary(): bool
  {
    return is_null($this->linked_student_id) && $this->linkedRecords()->exists();
  }

  // Only rows that represent a unique physical student (used for school-wide totals).
  public function scopeUniquePhysicalStudents($query)
  {
    return $query->whereNull('linked_student_id');
  }

  // All rows (this one + linked ones) belonging to the same physical student, primary first.
  public function allProgramRecords()
  {
    $primary = $this->linked_student_id ? $this->primaryRecord()->first() : $this;

    if (!$primary) {
      return collect([$this]);
    }

    return collect([$primary])->merge($primary->linkedRecords()->get());
  }
}
