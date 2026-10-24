<?php

namespace App\Audit;

use Illuminate\Http\UploadedFile;

/**
 * Makes arbitrary data safe to store in the audit trail:
 *  - secrets (passwords, tokens, OTPs...) become "[REDACTED]"
 *  - identity numbers keep only their last 3 characters
 *  - uploaded files are reduced to name / size / type
 *  - binary blobs and data: URIs are replaced by a size marker
 *  - very long strings are truncated
 *  - depth and item count are bounded
 */
class DataMasker
{
    public const REDACTED = '[REDACTED]';

    public static function maskKeys(): array
    {
        return array_map('strtolower', (array) config('audit.masked_keys', []));
    }

    public static function partialKeys(): array
    {
        return array_map('strtolower', (array) config('audit.partially_masked_keys', []));
    }

    public static function maxLength(): int
    {
        return (int) config('audit.max_value_length', 1000);
    }

    /**
     * @param  array<string|int, mixed>  $data
     * @return array<string|int, mixed>
     */
    public static function mask(array $data, int $maxDepth = 4, int $maxItems = 200, int $depth = 0): array
    {
        $out = [];
        $count = 0;

        foreach ($data as $key => $value) {
            if (++$count > $maxItems) {
                $out['…'] = '[' . (count($data) - $maxItems) . ' more items omitted]';
                break;
            }

            $out[$key] = self::maskValue((string) $key, $value, $maxDepth, $maxItems, $depth);
        }

        return $out;
    }

    public static function maskValue(string $key, mixed $value, int $maxDepth = 4, int $maxItems = 200, int $depth = 0): mixed
    {
        $lower = strtolower($key);

        if ($key !== '' && self::keyMatches($lower, self::maskKeys())) {
            return ($value === null || $value === '') ? $value : self::REDACTED;
        }

        if ($value instanceof UploadedFile) {
            return [
                '_file' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
                'type' => $value->getClientMimeType(),
            ];
        }

        if (is_array($value)) {
            if ($depth >= $maxDepth) {
                return '[nested data omitted]';
            }

            return self::mask($value, $maxDepth, $maxItems, $depth + 1);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? self::truncate((string) $value) : '[object ' . get_class($value) . ']';
        }

        if (is_string($value)) {
            if ($key !== '' && self::keyMatches($lower, self::partialKeys())) {
                return self::partial($value);
            }

            return self::scrubString($value);
        }

        return $value;
    }

    /** Exact-or-substring key match against a list of fragments. */
    private static function keyMatches(string $lowerKey, array $fragments): bool
    {
        foreach ($fragments as $fragment) {
            if ($fragment === '') {
                continue;
            }

            // Short fragments (otp, nin, pin) must match a whole word so
            // "option" or "mining" are never mistaken for secrets.
            if (strlen($fragment) <= 4) {
                if (preg_match('/(^|[^a-z0-9])' . preg_quote($fragment, '/') . '($|[^a-z0-9])/', $lowerKey)) {
                    return true;
                }
                continue;
            }

            if (str_contains($lowerKey, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private static function partial(string $value): string
    {
        $len = mb_strlen($value);

        return $len <= 3 ? str_repeat('•', $len) : str_repeat('•', min($len - 3, 8)) . mb_substr($value, -3);
    }

    private static function scrubString(string $value): string
    {
        // Inline images / files (signatures, photos).
        if (preg_match('#^data:[\w/+.-]+;base64,#i', $value)) {
            return '[inline file ' . strlen($value) . ' chars]';
        }

        // Not valid UTF-8 = binary.
        if ($value !== '' && !mb_check_encoding($value, 'UTF-8')) {
            return '[binary ' . strlen($value) . ' bytes]';
        }

        return self::truncate($value);
    }

    public static function truncate(string $value): string
    {
        $max = self::maxLength();

        if (mb_strlen($value) <= $max) {
            return $value;
        }

        return mb_substr($value, 0, $max) . '…[truncated ' . (mb_strlen($value) - $max) . ' chars]';
    }

    /**
     * JSON for storage; never throws. Returns null for null/empty input.
     */
    public static function toJson(?array $data): ?string
    {
        if ($data === null || $data === []) {
            return null;
        }

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            return json_encode(['_error' => 'value could not be encoded']);
        }

        // LONGTEXT holds 4GB, but a runaway entry helps nobody.
        if (strlen($json) > 500000) {
            return json_encode(['_truncated' => 'entry exceeded 500 KB', 'bytes' => strlen($json)]);
        }

        return $json;
    }
}
