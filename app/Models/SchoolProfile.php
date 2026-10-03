<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_type',
        'email',
        'website',
        'gender',
        'boarding_status',
        'name',
        'registration_code',
        'phone',
        'population',
        'motto',
        'vision',
        'admission_prefix',
        'admission_start',
        'admission_suffix',
        'logo',
        'head_teacher_name',
        'head_teacher_signature',
    ];

    // public function school()
    // {
    //     return $this->belongsTo(School::class);
    // }

    /**
     * Absolute path of this school's logo file, or null.
     *
     * The logo is uploaded to public/uploads/logos/ (SchoolController), but
     * the ID cards and report-card renderer only looked in
     * public/uploads/school_logos/, so a logo that WAS set always fell back
     * to the placeholder. Older uploads may sit in school_logos/ or the
     * storage disk, so every known location is tried.
     */
    public function logoAbsolutePath(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        $name = ltrim((string) $this->logo, '/');
        $candidates = [
            public_path('uploads/logos/' . $name),
            public_path('uploads/school_logos/' . $name),
            public_path($name),
            public_path('storage/' . $name),
            storage_path('app/public/' . $name),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /** Logo as a public URL (browser views), or null. */
    public function logoPublicUrl(): ?string
    {
        $path = $this->logoAbsolutePath();
        if (!$path) {
            return null;
        }

        $publicRoot = str_replace('\\', '/', public_path()) . '/';
        $normalised = str_replace('\\', '/', $path);

        if (strpos($normalised, $publicRoot) === 0) {
            return asset(substr($normalised, strlen($publicRoot)));
        }

        return $this->logoDataUri(); // outside public/ — inline it
    }

    /** Logo as a base64 data URI (PDF-safe), or null. */
    public function logoDataUri(): ?string
    {
        $path = $this->logoAbsolutePath();
        if (!$path) {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/png';
        $data = @file_get_contents($path);

        return $data === false ? null : 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}
