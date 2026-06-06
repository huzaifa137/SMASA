<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    protected $table = "library_members";

    protected $fillable = [
        "school_id",
        "member_type",
        "member_id",
        "library_card_number",
        "membership_date",
        "expiry_date",
        "max_books_allowed",
        "max_days_allowed",
        "status",
        "suspension_reason",
        "added_by"
    ];

    protected $casts = [
        "membership_date" => "date",
        "expiry_date" => "date"
    ];

    public function getNameAttribute()
    {
        if ($this->member_type === "student") {
            $student = Student::find($this->member_id);
            return $student ? trim(($student->firstname ?? "") . " " . ($student->lastname ?? "")) : "Unknown";
        }
        $teacher = Teacher::find($this->member_id);
        return $teacher ? trim(($teacher->firstname ?? "") . " " . ($teacher->surname ?? "")) : "Unknown";
    }

    public function borrowings()
    {
        return $this->hasMany(LibraryBorrowing::class, "member_id");
    }

    public function activeBorrowings()
    {
        return $this->hasMany(LibraryBorrowing::class, "member_id")
            ->whereIn("status", ["borrowed", "overdue"]);
    }

    public function fines()
    {
        return $this->hasMany(LibraryFine::class, "member_id");
    }

    public function unpaidFines()
    {
        return $this->hasMany(LibraryFine::class, "member_id")->where("status", "unpaid");
    }

    public function canBorrow()
    {
        if ($this->status !== "active")
            return false;
        if ($this->activeBorrowings()->count() >= $this->max_books_allowed)
            return false;
        if ($this->unpaidFines()->sum("amount") > 0)
            return false;
        return true;
    }

    // ========== ADD THESE MISSING METHODS ==========

    /**
     * Scope a query to filter by school
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope a query to get only active members
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the member model (Student or Teacher)
     */
    public function getMemberModelAttribute()
    {
        if ($this->member_type === 'student') {
            return Student::find($this->member_id);
        }
        return Teacher::find($this->member_id);
    }

    /**
     * Get reservations for this member
     */
    public function reservations()
    {
        return $this->hasMany(LibraryReservation::class, 'member_id');
    }

    /**
     * Check if member has active reservations
     */
    public function hasActiveReservations()
    {
        return $this->reservations()
            ->whereIn('status', ['pending', 'ready'])
            ->exists();
    }

    /**
     * Get total fines amount (unpaid)
     */
    public function getTotalFinesAttribute()
    {
        return $this->unpaidFines()->sum('amount');
    }

    /**
     * Get current borrowed count
     */
    public function getCurrentBorrowedCountAttribute()
    {
        return $this->activeBorrowings()->count();
    }

    /**
     * Check if member can reserve books
     */
    public function canReserve()
    {
        if ($this->status !== 'active') return false;
        if ($this->hasActiveReservations()) return false;
        return true;
    }
}