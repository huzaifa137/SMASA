<?php

namespace App\Support;

/**
 * Wording + formatting helpers for the Arabic report card design
 * ('arabic-classic' -> resources/views/Examination/passslips/slip-arabic.blade.php).
 *
 * The card is bilingual: every label exists in Arabic and in English and the
 * person printing it can flip between the two from the toolbar
 * (?card_lang=ar|en). Arabic is the default.
 *
 * Nothing here touches the database, so it is safe to call from any view.
 */
class ArabicReportCard
{
    /** The Design Template key stored in passslip_settings.template for a class. */
    public const TEMPLATE_KEY = 'arabic-classic';

    public const LANGS = ['ar', 'en'];

    /**
     * Resolve the language the card is shown in. Arabic unless English was
     * explicitly asked for. Deliberately does NOT read the legacy ?lang=
     * switch of the Report Cards page: that one defaults to "en" and is baked
     * into every link there, so honouring it would make this design open in
     * English every time.
     */
    public static function lang($requested): string
    {
        $requested = strtolower(trim((string) $requested));

        return in_array($requested, self::LANGS, true) ? $requested : 'ar';
    }

    /** True when a stored/requested Design Template key is the Arabic design. */
    public static function isTemplateKey($template): bool
    {
        return is_string($template) && str_starts_with($template, 'arabic-');
    }

    /** Every label on the card, in the requested language. */
    public static function labels(string $lang): array
    {
        $t = [
            'ar' => [
                'title' => 'كشف الدرجات النهائية للشعب',
                'academic_year' => 'العام الدراسي',
                'term' => 'الفصل',
                'based_on' => 'بناءً على ما تم دراسته',
                'name' => 'الاسم',
                'class' => 'الصف',
                'year' => 'السنة',
                'position' => 'الترتيب',
                'level' => 'المستوى',
                'section' => 'القسم',
                'subject' => 'المواد الدراسية',
                'class_average' => 'معدل الصف',
                'obtained' => 'الدرجة المحصّلة',
                'total' => 'مجموع الدرجات',
                'remarks' => 'الملاحظات والتعليقات',
                'grand_total' => 'المجموع الكلي',
                'note' => 'ملاحظة',
                'note_1' => 'على ولي الأمر الاهتمام بمتابعة درجات ابنه / ابنته والعمل على تحسينها.',
                'note_2' => 'يعتبر هذا الكشف تقييماً أولياً ولا يعتمد بشكل نهائي إلا بعد اعتماد النتائج النهائية.',
                'note_3' => 'في حالة وجود أي خطأ يرجى مراجعة إدارة المدرسة خلال فترة محددة.',
                'director' => 'المدير',
                'date' => 'التاريخ',
                'photo' => 'صورة',
                'of' => 'من',
                'print' => 'طباعة / حفظ PDF',
                'download' => 'تنزيل PDF',
                'back' => 'رجوع',
            ],
            'en' => [
                'title' => 'Final Grades Report',
                'academic_year' => 'Academic Year',
                'term' => 'Term',
                'based_on' => 'Based on what has been studied',
                'name' => 'Name',
                'class' => 'Class',
                'year' => 'Year',
                'position' => 'Position',
                'level' => 'Level',
                'section' => 'Section',
                'subject' => 'Subjects',
                'class_average' => 'Class Average',
                'obtained' => 'Marks Obtained',
                'total' => 'Total Marks',
                'remarks' => 'Remarks & Comments',
                'grand_total' => 'Grand Total',
                'note' => 'Note',
                'note_1' => 'Parents / guardians should follow up their child\'s marks and help to improve them.',
                'note_2' => 'This report is a preliminary assessment and is final only once the final results are approved.',
                'note_3' => 'If you find any error, please contact the school administration within the stated period.',
                'director' => 'Head Teacher',
                'date' => 'Date',
                'photo' => 'PHOTO',
                'of' => 'of',
                'print' => 'Print / Save PDF',
                'download' => 'Download PDF',
                'back' => 'Back',
            ],
        ];

        return $t[$lang] ?? $t['ar'];
    }

    /**
     * Numbers for display. In Arabic the digits become Arabic-Indic
     * (٠١٢٣٤٥٦٧٨٩) and the decimal point the Arabic one (٫), unless
     * $eastern is false (some schools prefer 0-9 inside Arabic text).
     */
    public static function digits($value, string $lang, bool $eastern = true): string
    {
        $value = (string) $value;

        if ($lang !== 'ar' || !$eastern) {
            return $value;
        }

        return strtr($value, [
            '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
            '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩',
            '.' => '٫',
        ]);
    }

    /** "Term I/II/III" in Arabic or English from any stored term form (see Term::number()). */
    public static function termLabel($term, string $lang): string
    {
        $n = Term::number($term);

        if ($n === null) {
            return trim((string) $term);
        }

        if ($lang === 'ar') {
            return [1 => 'الفصل الدراسي الأول', 2 => 'الفصل الدراسي الثاني', 3 => 'الفصل الدراسي الثالث'][$n] ?? (string) $term;
        }

        return Term::LABELS[$n] ?? (string) $term;
    }

    /**
     * The grading remark in the card language. Remarks come from each school's
     * own assessment scale (free text, normally English), so the common ones
     * are translated and anything else is printed exactly as entered.
     */
    public static function remark(?string $remark, ?string $grade, string $lang): string
    {
        $remark = trim((string) $remark);
        $grade = trim((string) $grade);

        if ($remark === '' || $remark === '—' || $remark === '-') {
            return $grade !== '' && $grade !== '—' ? $grade : '';
        }

        if ($lang !== 'ar') {
            return $remark;
        }

        $key = preg_replace('/[^a-z ]+/', '', strtolower($remark));
        $key = trim(preg_replace('/\s+/', ' ', $key));

        $map = [
            'outstanding' => 'متفوق', 'excellent' => 'متفوق', 'exceptional' => 'متفوق',
            'distinction' => 'متفوق', 'superior' => 'متفوق', 'very high' => 'متفوق',
            'very good' => 'جيد جداً', 'high' => 'جيد جداً', 'credit' => 'جيد جداً',
            'good' => 'جيد', 'moderate' => 'جيد',
            'fair' => 'مقبول', 'satisfactory' => 'مقبول', 'average' => 'مقبول',
            'acceptable' => 'مقبول', 'pass' => 'ناجح', 'passed' => 'ناجح',
            'weak' => 'ضعيف', 'poor' => 'ضعيف', 'low' => 'ضعيف',
            'needs improvement' => 'يحتاج إلى تحسين', 'below average' => 'ضعيف',
            'fail' => 'راسب', 'failed' => 'راسب', 'very poor' => 'ضعيف جداً',
        ];

        return $map[$key] ?? $remark;
    }
}
