<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryFine extends Model
{
    protected $table = "library_fines";
    
    protected $fillable = [
        "school_id", "borrowing_id", "member_id", "amount", "overdue_days",
        "status", "paid_date", "waive_reason", "processed_by"
    ];
    
    protected $casts = [
        "amount" => "decimal:2",
        "paid_date" => "date"
    ];
    
    public function borrowing()
    {
        return $this->belongsTo(LibraryBorrowing::class, "borrowing_id");
    }
    
    public function member()
    {
        return $this->belongsTo(LibraryMember::class, "member_id");
    }
}
