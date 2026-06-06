<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryReservation extends Model
{
    protected $table = "library_reservations";
    
    protected $fillable = [
        "school_id", "book_id", "member_id", "reservation_number",
        "reservation_date", "expiry_date", "status", "notes"
    ];
    
    protected $casts = [
        "reservation_date" => "date",
        "expiry_date" => "date"
    ];
    
    public function book()
    {
        return $this->belongsTo(LibraryBook::class, "book_id");
    }
    
    public function member()
    {
        return $this->belongsTo(LibraryMember::class, "member_id");
    }
}
