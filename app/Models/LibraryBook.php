<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model
{
    protected $table = "library_books";

    protected $fillable = [
        "school_id",
        "title",
        "isbn",
        "author_id",
        "category_id",
        "subject_id",
        "publisher",
        "publication_year",
        "edition",
        "language",
        "total_copies",
        "available_copies",
        "location",
        "price",
        "description",
        "cover_image",
        "ebook_file",
        "has_ebook",
        "is_active",
        "added_by"
    ];

    protected $casts = [
        "has_ebook" => "boolean",
        "is_active" => "boolean",
        "price" => "decimal:2"
    ];

    public function author()
    {
        return $this->belongsTo(LibraryAuthor::class, "author_id");
    }

    public function category()
    {
        return $this->belongsTo(LibraryCategory::class, "category_id");
    }

    public function subject()
    {
        return $this->belongsTo(LibrarySubject::class, "subject_id");
    }

    public function borrowings()
    {
        return $this->hasMany(LibraryBorrowing::class, "book_id");
    }

    public function reservations()
    {
        return $this->hasMany(LibraryReservation::class, "book_id");
    }

    public function activeBorrowings()
    {
        return $this->hasMany(LibraryBorrowing::class, "book_id")
            ->whereIn("status", ["borrowed", "overdue"]);
    }

    public function getIsAvailableAttribute()
    {
        return $this->available_copies > 0;
    }

    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where("school_id", $schoolId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('isbn', 'like', "%{$term}%")
                ->orWhere('publisher', 'like', "%{$term}%")
                ->orWhereHas('author', fn($a) => $a->where('name', 'like', "%{$term}%"))
                ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%"))
                ->orWhereHas('subject', fn($s) => $s->where('name', 'like', "%{$term}%"));
        });
    }
}
