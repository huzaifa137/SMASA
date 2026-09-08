<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * StudentPhotoImportService
 * ─────────────────────────────────────────────────────────────────────────
 * Bulk-matches a folder of student photos (named after LIN No., Registration
 * Number, or full name) to Student rows within one class + stream, then
 * standardizes and saves them.
 *
 * The match is always run twice from the caller's point of view:
 *   1. dryRun = true  → nothing is written; every file gets a status/reason
 *      so the admin can fix bad file names or bad images before anything
 *      touches the database or disk.
 *   2. dryRun = false → only files that pass every check ("ready") are
 *      processed and saved. Anything with an error is skipped and reported
 *      again, never partially written.
 *
 * Matching criteria (any one, chosen per import by the admin):
 *   - lin  → students.admission_number ("LIN No.")   — unique in the DB
 *   - reg  → students.registration_number ("Reg No.") — unique in the DB
 *   - name → firstname + lastname (either order)      — not unique, so any
 *            filename that matches more than one student in the chosen
 *            class/stream is rejected and must be renamed using LIN No./
 *            Registration Number instead.
 *
 * Every photo that is saved is re-encoded to a normalized JPEG: auto-rotated
 * from EXIF, downscaled to a sane max size, and compressed to a target file
 * size — so slips, ID cards, and the students list never have to deal with
 * oversized or sideways uploads.
 */
class StudentPhotoImportService
{
    /** Longest edge, in pixels, that a saved photo is allowed to have. */
    private const MAX_DIMENSION = 600;

    /** Anything smaller than this on either edge is rejected outright. */
    private const MIN_DIMENSION = 120;

    private const JPEG_QUALITY_START = 85;
    private const JPEG_QUALITY_MIN = 50;
    private const TARGET_MAX_BYTES = 200 * 1024; // ~200KB per photo

    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const KNOWN_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * @param  UploadedFile[]  $files
     * @return array{status:string,dry_run:bool,message:string,results:array,summary:array}
     */
    public function run(
        int $schoolId,
        string $classId,
        string $streamId,
        string $matchBy,
        array $files,
        bool $dryRun
    ): array {
        $students = Student::where('school_id', $schoolId)
            ->where('senior', $classId)
            ->where('stream', $streamId)
            ->get(['id', 'firstname', 'lastname', 'admission_number', 'registration_number']);

        if ($students->isEmpty()) {
            return [
                'status' => 'error',
                'dry_run' => $dryRun,
                'message' => 'No students found for the selected class and stream.',
                'results' => [],
                'summary' => ['total' => 0, 'ready' => 0, 'imported' => 0, 'errors' => 0],
            ];
        }

        [$lookup, $duplicateKeys] = $this->buildLookup($students, $matchBy);

        $results = [];
        $matchedStudentIds = [];

        foreach ($files as $file) {
            $results[] = $this->evaluateFile($file, $matchBy, $lookup, $duplicateKeys, $matchedStudentIds);
        }

        if (!$dryRun) {
            foreach ($results as &$entry) {
                if ($entry['status'] !== 'ready') {
                    continue;
                }

                try {
                    $student = Student::find($entry['student']['id']);

                    if (!$student) {
                        throw new \RuntimeException('Student no longer exists.');
                    }

                    $this->saveStudentPhoto($student, $entry['_file']);
                    $entry['status'] = 'imported';
                } catch (\Throwable $e) {
                    Log::error('Bulk student photo import: failed to save photo', [
                        'student_id' => $entry['student']['id'] ?? null,
                        'file' => $entry['file'],
                        'error' => $e->getMessage(),
                    ]);

                    $entry['status'] = 'error';
                    $entry['reason'] = 'Could not save this photo (' . $e->getMessage() . '). Please try re-uploading it.';
                }
            }
            unset($entry);
        }

        $readyCount = 0;
        $importedCount = 0;
        $errorCount = 0;

        $publicResults = array_map(function ($entry) use (&$readyCount, &$importedCount, &$errorCount) {
            match ($entry['status']) {
                'ready' => $readyCount++,
                'imported' => $importedCount++,
                default => $errorCount++,
            };

            unset($entry['_file']);

            return $entry;
        }, $results);

        $message = $dryRun
            ? "Checked " . count($results) . " photo(s): {$readyCount} ready to import, {$errorCount} need fixing."
            : "{$importedCount} photo(s) imported successfully." . ($errorCount ? " {$errorCount} skipped due to errors — see below." : '');

        return [
            'status' => 'ok',
            'dry_run' => $dryRun,
            'message' => $message,
            'results' => $publicResults,
            'summary' => [
                'total' => count($results),
                'ready' => $readyCount,
                'imported' => $importedCount,
                'errors' => $errorCount,
            ],
        ];
    }

    /**
     * Evaluate one uploaded file: resolve which student it belongs to (if
     * any) and, if a student is found, validate the image itself. Never
     * writes anything — pure inspection.
     */
    private function evaluateFile(
        UploadedFile $file,
        string $matchBy,
        array $lookup,
        array $duplicateKeys,
        array &$matchedStudentIds
    ): array {
        $originalName = $file->getClientOriginalName();
        $identifier = trim(pathinfo($originalName, PATHINFO_FILENAME));
        $key = $this->normalizeKey($identifier, $matchBy);

        $entry = [
            'file' => $originalName,
            'identifier' => $identifier,
            'status' => 'error',
            'reason' => null,
            'student' => null,
        ];

        if ($identifier === '') {
            $entry['reason'] = 'Could not read a ' . $this->criterionLabel($matchBy) . ' from this file name.';

            return $entry;
        }

        if (isset($duplicateKeys[$key])) {
            $entry['reason'] = 'Multiple students in this class/stream share this ' .
                $this->criterionLabel($matchBy) . " (\"{$identifier}\"). Rename the file using LIN No. or " .
                'Registration Number instead so it is unique.';

            return $entry;
        }

        if (!isset($lookup[$key])) {
            $entry['reason'] = 'No student found with ' . $this->criterionLabel($matchBy) .
                " \"{$identifier}\" in this class/stream.";

            return $entry;
        }

        $student = $lookup[$key];
        $entry['student'] = [
            'id' => $student->id,
            'name' => trim($student->firstname . ' ' . $student->lastname),
            'admission_number' => $student->admission_number,
            'registration_number' => $student->registration_number,
        ];

        if (isset($matchedStudentIds[$student->id])) {
            $entry['reason'] = 'Another file ("' . $matchedStudentIds[$student->id] . '") already matches ' .
                $entry['student']['name'] . '. Remove the duplicate photo.';

            return $entry;
        }

        $imageCheck = $this->validateImage($file);

        if ($imageCheck !== true) {
            $entry['reason'] = $imageCheck;

            return $entry;
        }

        $entry['status'] = 'ready';
        $entry['_file'] = $file;
        $matchedStudentIds[$student->id] = $originalName;

        return $entry;
    }

    /**
     * Build a normalized-key → Student lookup for the chosen matching
     * criterion, plus a set of keys that are ambiguous (shared by more than
     * one student) and must therefore be rejected rather than guessed at.
     *
     * @return array{0: array<string, Student>, 1: array<string, true>}
     */
    private function buildLookup(iterable $students, string $matchBy): array
    {
        $lookup = [];
        $duplicates = [];

        foreach ($students as $student) {
            $keys = [];

            if ($matchBy === 'lin') {
                if (!empty($student->admission_number)) {
                    $keys[] = $this->normalizeKey($student->admission_number, $matchBy);
                }
            } elseif ($matchBy === 'reg') {
                if (!empty($student->registration_number)) {
                    $keys[] = $this->normalizeKey($student->registration_number, $matchBy);
                }
            } else {
                $full = trim($student->firstname . ' ' . $student->lastname);
                $reversed = trim($student->lastname . ' ' . $student->firstname);

                foreach (array_unique([$full, $reversed]) as $variant) {
                    $keys[] = $this->normalizeKey($variant, $matchBy);
                }
            }

            foreach (array_unique($keys) as $key) {
                if ($key === '') {
                    continue;
                }

                if (isset($lookup[$key])) {
                    if ($lookup[$key]->id !== $student->id) {
                        $duplicates[$key] = true;
                    }
                } else {
                    $lookup[$key] = $student;
                }
            }
        }

        // A key that is ambiguous should never resolve to either student.
        foreach (array_keys($duplicates) as $dupeKey) {
            unset($lookup[$dupeKey]);
        }

        return [$lookup, $duplicates];
    }

    private function normalizeKey(string $identifier, string $matchBy): string
    {
        if ($matchBy === 'name') {
            $clean = preg_replace('/[_\-]+/', ' ', $identifier);
            $clean = preg_replace('/\s+/', ' ', trim((string) $clean));

            return mb_strtolower((string) $clean);
        }

        return mb_strtolower(trim($identifier));
    }

    private function criterionLabel(string $matchBy): string
    {
        return match ($matchBy) {
            'lin' => 'LIN No.',
            'reg' => 'Registration Number',
            default => 'name',
        };
    }

    /**
     * Confirm the upload is actually a readable image of a sane size before
     * we let it anywhere near GD or the students table.
     *
     * @return true|string true when valid, otherwise a user-facing reason
     */
    private function validateImage(UploadedFile $file): true|string
    {
        if (!$file->isValid()) {
            return 'The upload did not complete correctly. Please re-upload this photo.';
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());

        if (!in_array($ext, self::KNOWN_EXTENSIONS, true)) {
            return "Unsupported file type \".{$ext}\". Use JPG, PNG, GIF or WEBP.";
        }

        $info = @getimagesize($file->getRealPath());

        if ($info === false) {
            return 'This is not a valid image — it may be corrupted or renamed from another file type.';
        }

        [$width, $height] = $info;
        $mime = $info['mime'] ?? '';

        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            return "Unsupported image format ({$mime}).";
        }

        if ($mime === 'image/webp' && !function_exists('imagecreatefromwebp')) {
            return 'This server\'s image library does not support WEBP. Please convert to JPG or PNG.';
        }

        if ($width < self::MIN_DIMENSION || $height < self::MIN_DIMENSION) {
            $min = self::MIN_DIMENSION;

            return "Image is too small ({$width}x{$height}px). Minimum is {$min}x{$min}px.";
        }

        return true;
    }

    /**
     * Standardize the photo (auto-orient, resize, compress) and save it to
     * disk under the convention every other part of the app already reads:
     * uploads/studentPhotos/{student_id}.jpg, referenced by students.student_photo.
     */
    private function saveStudentPhoto(Student $student, UploadedFile $file): void
    {
        $destinationDir = public_path('uploads/studentPhotos');

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $binary = $this->renderStandardizedJpeg($file->getRealPath());

        // Remove any stale photo for this student under any extension,
        // mirroring the cleanup already done by the single-student uploads.
        foreach (self::KNOWN_EXTENSIONS as $ext) {
            $old = $destinationDir . '/' . $student->id . '.' . $ext;
            if (file_exists($old)) {
                @unlink($old);
            }
        }

        file_put_contents($destinationDir . '/' . $student->id . '.jpg', $binary);

        $student->student_photo = (string) $student->id;
        $student->save();
    }

    /**
     * Load, auto-orient, downscale and re-encode an image to a
     * size-capped JPEG. Returns the raw binary JPEG data.
     */
    private function renderStandardizedJpeg(string $path): string
    {
        $info = getimagesize($path);
        $mime = $info['mime'] ?? '';

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (!$source) {
            throw new \RuntimeException('unable to read image data');
        }

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $orientation = $exif['Orientation'] ?? 1;
            $source = $this->applyOrientation($source, $orientation);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $longEdge = max($width, $height);

        if ($longEdge > self::MAX_DIMENSION) {
            $ratio = self::MAX_DIMENSION / $longEdge;
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Final output is always JPEG (no alpha channel), so flatten any
        // transparency (PNG/GIF/WEBP) onto a white background first.
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
        imagealphablending($canvas, true);

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        $quality = self::JPEG_QUALITY_START;

        do {
            ob_start();
            imagejpeg($canvas, null, $quality);
            $binary = ob_get_clean();
            $quality -= 10;
        } while (strlen($binary) > self::TARGET_MAX_BYTES && $quality >= self::JPEG_QUALITY_MIN);

        imagedestroy($canvas);

        return $binary;
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function applyOrientation($image, int $orientation)
    {
        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
