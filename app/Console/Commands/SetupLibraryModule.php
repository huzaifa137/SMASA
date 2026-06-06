<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SetupLibraryModule extends Command
{
    protected $signature = 'library:setup';
    protected $description = 'Setup complete library module with migrations and models';

    public function handle()
    {
        $this->info('Setting up Library Module...');
        $this->newLine();

        // Create Models directory if not exists
        $modelsPath = app_path('Models');
        if (!File::exists($modelsPath)) {
            File::makeDirectory($modelsPath, 0755, true);
        }

        // Model files content
        $models = [
            'LibraryCategory.php' => $this->getLibraryCategoryModel(),
            'LibraryAuthor.php' => $this->getLibraryAuthorModel(),
            'LibrarySubject.php' => $this->getLibrarySubjectModel(),
            'LibraryBook.php' => $this->getLibraryBookModel(),
            'LibraryMember.php' => $this->getLibraryMemberModel(),
            'LibraryBorrowing.php' => $this->getLibraryBorrowingModel(),
            'LibraryReservation.php' => $this->getLibraryReservationModel(),
            'LibraryFine.php' => $this->getLibraryFineModel(),
            'LibraryBookRequest.php' => $this->getLibraryBookRequestModel(),
            'LibrarySetting.php' => $this->getLibrarySettingModel(),
        ];

        // Create Models
        $this->info('Creating Models...');
        foreach ($models as $filename => $content) {
            $path = app_path("Models/{$filename}");
            if (!File::exists($path)) {
                File::put($path, $content);
                $this->line("Created: {$filename}");
            } else {
                $this->line("Skipped (exists): {$filename}");
            }
        }

        $this->newLine();

        // Create Migrations
        $this->info('Creating Migrations...');

        $migrations = [
            '2024_01_01_000001_create_library_categories_table.php' => $this->getCategoriesMigration(),
            '2024_01_01_000002_create_library_authors_table.php' => $this->getAuthorsMigration(),
            '2024_01_01_000003_create_library_subjects_table.php' => $this->getSubjectsMigration(),
            '2024_01_01_000004_create_library_books_table.php' => $this->getBooksMigration(),
            '2024_01_01_000005_create_library_members_table.php' => $this->getMembersMigration(),
            '2024_01_01_000006_create_library_borrowings_table.php' => $this->getBorrowingsMigration(),
            '2024_01_01_000007_create_library_reservations_table.php' => $this->getReservationsMigration(),
            '2024_01_01_000008_create_library_fines_table.php' => $this->getFinesMigration(),
            '2024_01_01_000009_create_library_book_requests_table.php' => $this->getBookRequestsMigration(),
            '2024_01_01_000010_create_library_settings_table.php' => $this->getSettingsMigration(),
        ];

        $migrationsPath = database_path('migrations');

        foreach ($migrations as $filename => $content) {
            $path = $migrationsPath . '/' . $filename;
            if (!File::exists($path)) {
                File::put($path, $content);
                $this->line("  ✅ Created: {$filename}");
            } else {
                $this->line("  ⏭️  Skipped (exists): {$filename}");
            }
        }

        $this->newLine();

        // Run migrations
        if ($this->confirm('Run migrations now?')) {
            $this->info('Running migrations...');
            Artisan::call('migrate');
            $this->line(Artisan::output());
        }

        $this->newLine();
        $this->info('Library module setup completed successfully!');

        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Run: php artisan library:seed (if you have seeders)');
        $this->line('  2. Create controllers for library management');
        $this->line('  3. Add library routes to web.php');
        $this->line('  4. Create views for library operations');
    }

    // Model content methods (add all your model code here)
    private function getLibraryCategoryModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryCategory extends Model
{
    protected $table = "library_categories";
    
    protected $fillable = [
        "school_id", "name", "slug", "description", "color", "is_active"
    ];
    
    protected $casts = [
        "is_active" => "boolean"
    ];
    
    public function books()
    {
        return $this->hasMany(LibraryBook::class, "category_id");
    }
    
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
    
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where("school_id", $schoolId);
    }
}
';
    }

    private function getLibraryAuthorModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryAuthor extends Model
{
    protected $table = "library_authors";
    
    protected $fillable = [
        "school_id", "name", "bio", "nationality", "is_active"
    ];
    
    protected $casts = [
        "is_active" => "boolean"
    ];
    
    public function books()
    {
        return $this->hasMany(LibraryBook::class, "author_id");
    }
    
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
    
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where("school_id", $schoolId);
    }
}
';
    }

    private function getLibrarySubjectModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySubject extends Model
{
    protected $table = "library_subjects";
    
    protected $fillable = [
        "school_id", "name", "description", "is_active"
    ];
    
    protected $casts = [
        "is_active" => "boolean"
    ];
    
    public function books()
    {
        return $this->hasMany(LibraryBook::class, "subject_id");
    }
    
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
    
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where("school_id", $schoolId);
    }
}
';
    }

    private function getLibraryBookModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model
{
    protected $table = "library_books";
    
    protected $fillable = [
        "school_id", "title", "isbn", "author_id", "category_id", "subject_id",
        "publisher", "publication_year", "edition", "language", "total_copies",
        "available_copies", "location", "price", "description", "cover_image",
        "ebook_file", "has_ebook", "is_active", "added_by"
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
}
';
    }

    private function getLibraryMemberModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    protected $table = "library_members";
    
    protected $fillable = [
        "school_id", "member_type", "member_id", "library_card_number",
        "membership_date", "expiry_date", "max_books_allowed", "max_days_allowed",
        "status", "suspension_reason", "added_by"
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
        if ($this->status !== "active") return false;
        if ($this->activeBorrowings()->count() >= $this->max_books_allowed) return false;
        if ($this->unpaidFines()->sum("amount") > 0) return false;
        return true;
    }
}
';
    }

    private function getLibraryBorrowingModel()
    {
        return '<?php

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
';
    }

    private function getLibraryReservationModel()
    {
        return '<?php

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
';
    }

    private function getLibraryFineModel()
    {
        return '<?php

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
';
    }

    private function getLibraryBookRequestModel()
    {
        return '<?php

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
';
    }

    private function getLibrarySettingModel()
    {
        return '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $table = "library_settings";
    
    protected $fillable = [
        "school_id", "fine_per_day", "student_max_books", "teacher_max_books",
        "student_loan_days", "teacher_loan_days", "max_renewals",
        "enable_reservations", "enable_ebooks", "enable_recommendations"
    ];
    
    protected $casts = [
        "fine_per_day" => "decimal:2",
        "enable_reservations" => "boolean",
        "enable_ebooks" => "boolean",
        "enable_recommendations" => "boolean"
    ];
    
    public static function forSchool(int $schoolId): self
    {
        return self::firstOrCreate(
            ["school_id" => $schoolId],
            [
                "fine_per_day" => 100.00,
                "student_max_books" => 3,
                "teacher_max_books" => 5,
                "student_loan_days" => 14,
                "teacher_loan_days" => 30,
                "max_renewals" => 2,
                "enable_reservations" => true,
                "enable_ebooks" => true,
                "enable_recommendations" => true
            ]
        );
    }
}
';
    }

    // Migration content methods (add all your migration code here)
    private function getCategoriesMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_categories", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("name", 100);
            $table->string("slug", 120)->nullable();
            $table->text("description")->nullable();
            $table->string("color", 20)->default("#5351e4");
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->index(["school_id", "is_active"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_categories");
    }
};
';
    }

    private function getAuthorsMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_authors", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("name", 150);
            $table->text("bio")->nullable();
            $table->string("nationality", 80)->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->index("school_id");
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_authors");
    }
};
';
    }

    private function getSubjectsMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_subjects", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("name", 100);
            $table->text("description")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->index("school_id");
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_subjects");
    }
};
';
    }

    private function getBooksMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_books", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("title", 255);
            $table->string("isbn", 30)->nullable();
            $table->unsignedBigInteger("author_id")->nullable();
            $table->unsignedBigInteger("category_id")->nullable();
            $table->unsignedBigInteger("subject_id")->nullable();
            $table->string("publisher", 150)->nullable();
            $table->year("publication_year")->nullable();
            $table->string("edition", 50)->nullable();
            $table->string("language", 50)->default("English");
            $table->integer("total_copies")->default(1);
            $table->integer("available_copies")->default(1);
            $table->string("location", 100)->nullable();
            $table->decimal("price", 10, 2)->nullable();
            $table->text("description")->nullable();
            $table->string("cover_image")->nullable();
            $table->string("ebook_file")->nullable();
            $table->boolean("has_ebook")->default(false);
            $table->boolean("is_active")->default(true);
            $table->unsignedBigInteger("added_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "is_active"]);
            $table->index("isbn");
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_books");
    }
};
';
    }

    private function getMembersMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_members", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string("member_type", 20);
            $table->unsignedBigInteger("member_id");
            $table->string("library_card_number", 30)->unique();
            $table->date("membership_date");
            $table->date("expiry_date")->nullable();
            $table->integer("max_books_allowed")->default(3);
            $table->integer("max_days_allowed")->default(14);
            $table->enum("status", ["active", "suspended", "expired"])->default("active");
            $table->text("suspension_reason")->nullable();
            $table->unsignedBigInteger("added_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "member_type", "member_id"]);
            $table->index("library_card_number");
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_members");
    }
};
';
    }

    private function getBorrowingsMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_borrowings", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("book_id");
            $table->unsignedBigInteger("member_id");
            $table->string("borrow_number", 20)->unique();
            $table->date("borrow_date");
            $table->date("due_date");
            $table->date("return_date")->nullable();
            $table->integer("renewals")->default(0);
            $table->enum("status", ["borrowed", "returned", "overdue", "lost"])->default("borrowed");
            $table->text("notes")->nullable();
            $table->unsignedBigInteger("issued_by")->nullable();
            $table->unsignedBigInteger("returned_to")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
            $table->index(["book_id", "status"]);
            $table->index(["member_id", "status"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_borrowings");
    }
};
';
    }

    private function getReservationsMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_reservations", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("book_id");
            $table->unsignedBigInteger("member_id");
            $table->string("reservation_number", 20)->unique();
            $table->date("reservation_date");
            $table->date("expiry_date")->nullable();
            $table->enum("status", ["pending", "ready", "fulfilled", "cancelled", "expired"])->default("pending");
            $table->text("notes")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
            $table->index(["book_id", "member_id"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_reservations");
    }
};
';
    }

    private function getFinesMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_fines", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("borrowing_id");
            $table->unsignedBigInteger("member_id");
            $table->decimal("amount", 10, 2);
            $table->integer("overdue_days");
            $table->enum("status", ["unpaid", "paid", "waived"])->default("unpaid");
            $table->date("paid_date")->nullable();
            $table->text("waive_reason")->nullable();
            $table->unsignedBigInteger("processed_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
            $table->index(["member_id", "status"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_fines");
    }
};
';
    }

    private function getBookRequestsMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_book_requests", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->unsignedBigInteger("member_id");
            $table->string("book_title", 255);
            $table->string("author", 150)->nullable();
            $table->string("isbn", 30)->nullable();
            $table->string("publisher", 150)->nullable();
            $table->text("reason")->nullable();
            $table->enum("status", ["pending", "approved", "rejected", "fulfilled"])->default("pending");
            $table->text("admin_notes")->nullable();
            $table->unsignedBigInteger("reviewed_by")->nullable();
            $table->timestamps();
            $table->index(["school_id", "status"]);
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_book_requests");
    }
};
';
    }

    private function getSettingsMigration()
    {
        return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("library_settings", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id")->unique();
            $table->decimal("fine_per_day", 6, 2)->default(100.00);
            $table->integer("student_max_books")->default(3);
            $table->integer("teacher_max_books")->default(5);
            $table->integer("student_loan_days")->default(14);
            $table->integer("teacher_loan_days")->default(30);
            $table->integer("max_renewals")->default(2);
            $table->boolean("enable_reservations")->default(true);
            $table->boolean("enable_ebooks")->default(true);
            $table->boolean("enable_recommendations")->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("library_settings");
    }
};
';
    }
}