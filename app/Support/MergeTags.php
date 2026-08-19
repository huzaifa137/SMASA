<?php

namespace App\Support;

/**
 * Resolves `{{dot.path}}` merge tags inside a text element's `content`
 * against the render `$data` array (see ReportCardRenderer::dataForResult).
 *
 * Used by resources/views/report-cards/render.blade.php for every `text`
 * element, so a designer can drop tags like {{school_name}}, {{term}},
 * {{year}}, {{student.name}}, {{overall_grade}} etc. straight into any
 * text box on the canvas.
 *
 * Output is escaped (e.g(...)) before being echoed with {!! !!} in the
 * Blade partial, since the partial needs raw HTML to allow <br> inside a
 * "Motto" or address text block — resolve() itself never allows the DATA
 * to inject HTML, only literal merge-tag values, which are escaped here.
 */
class MergeTags
{
    public static function resolve(string $content, array $data): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function ($m) use ($data) {
            $value = data_get($data, $m[1]);

            if (is_array($value)) {
                return '';
            }

            return e($value ?? '');
        }, $content);
    }
}
