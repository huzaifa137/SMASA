<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryBookRequest extends Model
{
    protected $table = "library_book_requests";
    
    protected $fillable = [
        "school_id", "member_id", "book_title", "author", "isbn", "publisher",
        "reason", "status", "admin_notes", "reviewed_by"
    ];
    
    public function member()
    {
        return $this->belongsTo(LibraryMember::class, "member_id");
    }
}
