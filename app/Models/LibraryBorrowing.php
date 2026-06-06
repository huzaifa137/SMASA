<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LibraryBorrowing extends Model
{
    protected $table = "library_borrowings";
    
    protected $fillable = [
        "school_id", "book_id", "member_id", "borrow_number",
        "borrow_date", "due_date", "return_date", "renewals",
        "status", "notes", "issued_by", "returned_to"
    ];
    
    protected $casts = [
        "borrow_date" => "date",
        "due_date" => "date",
        "return_date" => "date"
    ];
    
    public function book()
    {
        return $this->belongsTo(LibraryBook::class, "book_id");
    }
    
    public function member()
    {
        return $this->belongsTo(LibraryMember::class, "member_id");
    }
    
    public function fine()
    {
        return $this->hasOne(LibraryFine::class, "borrowing_id");
    }
    
    public function getOverdueDaysAttribute()
    {
        if ($this->return_date) {
            return max(0, $this->due_date->diffInDays($this->return_date, false) * -1);
        }
        if (now()->gt($this->due_date)) {
            return (int) now()->diffInDays($this->due_date);
        }
        return 0;
    }
    
    public function isOverdue()
    {
        return !$this->return_date && now()->gt($this->due_date);
    }
}
