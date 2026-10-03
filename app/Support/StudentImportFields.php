<?php

namespace App\Support;

/**
 * Single source of truth for the "optional" Student bio-data columns the
 * bulk import/update template can be toggled to include — used by the
 * template exporter (StudentBulkTemplate), the importer/updater
 * (StudentBulkImport) and the bulk-import-students form so all three
 * agree on the same key ↔ heading pairs.
 *
 * `key` is the students table column name and also the array key
 * StudentBulkImport reads the value back under. `label` is the Excel
 * column heading shown to the school. This works both ways without any
 * extra mapping because Maatwebsite Excel's default heading-row
 * formatter slugs a heading back to snake_case before handing rows to
 * ToCollection — e.g. "Home Address" → "home_address", "Guardian
 * Phone" → "guardian_phone" — which already matches every key below.
 *
 * firstname / lastname / gender are NOT here — those three are always
 * present on the template (see StudentBulkTemplate::headings()) and are
 * handled directly by StudentBulkImport, not through this optional list.
 */
class StudentImportFields
{
    public const FIELDS = [
        ['key' => 'admission_number', 'label' => 'LIN No.', 'group' => 'Identification'],
        ['key' => 'paycode', 'label' => 'Paycode', 'group' => 'Identification'],
        ['key' => 'primary_contact', 'label' => 'Primary Contact', 'group' => 'Contact & Address'],
        ['key' => 'other_contact', 'label' => 'Other Contact', 'group' => 'Contact & Address'],
        ['key' => 'home_address', 'label' => 'Home Address', 'group' => 'Contact & Address'],
        ['key' => 'date_of_birth', 'label' => 'Date of Birth', 'group' => 'Personal'],
        ['key' => 'place_of_birth', 'label' => 'Place of Birth', 'group' => 'Personal'],
        ['key' => 'nationality', 'label' => 'Nationality', 'group' => 'Personal'],
        ['key' => 'birth_certificate_entry_number', 'label' => 'Birth Cert. Entry No.', 'group' => 'Personal'],
        ['key' => 'date_of_admission', 'label' => 'Date of Admission', 'group' => 'Academic'],
        ['key' => 'previous_school', 'label' => 'Previous School', 'group' => 'Academic'],
        ['key' => 'primary_school_name', 'label' => 'Primary School Name', 'group' => 'Academic'],
        ['key' => 'ple_score', 'label' => 'PLE Score', 'group' => 'Academic'],
        ['key' => 'uce_score', 'label' => 'UCE Score', 'group' => 'Academic'],
        ['key' => 'guardian_names', 'label' => 'Guardian Name(s)', 'group' => 'Guardian'],
        ['key' => 'relation', 'label' => 'Guardian Relation', 'group' => 'Guardian'],
        ['key' => 'guardian_phone', 'label' => 'Guardian Phone', 'group' => 'Guardian'],
        ['key' => 'guardian_email', 'label' => 'Guardian Email', 'group' => 'Guardian'],
        ['key' => 'medical_history', 'label' => 'Medical History', 'group' => 'Other'],
        ['key' => 'comments', 'label' => 'Comments', 'group' => 'Other'],
    ];

    /** decimal columns — need numeric cleaning on the way in. */
    public const NUMERIC_KEYS = ['ple_score', 'uce_score'];

    /**
     * Other headings a school's own sheet may use for a field. The Excel
     * reader turns every heading into a slug ("LIN No." -> "lin_no",
     * "Birth Cert. Entry No." -> "birth_cert_entry_no"), which does NOT
     * equal the database column for several fields (LIN No. is stored in
     * `admission_number`), so those columns were silently ignored.
     * Matching ignores case, spaces and punctuation, so "LIN No.", "lin_no"
     * and "LIN NO" are all the same heading here.
     */
    public const ALIASES = [
        'admission_number' => ['LIN', 'LIN No', 'LIN Number', 'Admission No', 'Admission Number'],
        'paycode' => ['Pay Code'],
        'primary_contact' => ['Phone', 'Contact', 'Phone Number', 'Telephone', 'Tel', 'Mobile'],
        'other_contact' => ['Other Phone', 'Secondary Contact', 'Alt Contact', 'Alternative Contact'],
        'home_address' => ['Address', 'Residence'],
        'date_of_birth' => ['DOB', 'Birth Date', 'Birthday'],
        'place_of_birth' => ['Birth Place', 'POB'],
        'birth_certificate_entry_number' => ['Birth Cert Entry No', 'Birth Certificate No', 'Birth Certificate Number', 'Birth Cert No'],
        'date_of_admission' => ['Admission Date', 'Date Admitted'],
        'previous_school' => ['Prev School', 'Former School', 'Last School'],
        'primary_school_name' => ['Primary School'],
        'ple_score' => ['PLE', 'PLE Marks', 'PLE Aggregate'],
        'uce_score' => ['UCE', 'UCE Marks', 'UCE Aggregate'],
        'guardian_names' => ['Guardian Name', 'Guardian', 'Parent Name', 'Parent'],
        'relation' => ['Guardian Relation', 'Relationship', 'Guardian Relationship', 'Parent Relation'],
        'guardian_phone' => ['Guardian Contact', 'Parent Phone', 'Parent Contact', 'Guardian Tel'],
        'guardian_email' => ['Parent Email'],
        'medical_history' => ['Medical', 'Medical Notes'],
        'comments' => ['Comment', 'Remarks', 'Notes'],
    ];

    /** Lower-case, letters+digits only: the comparison form of a heading. */
    public static function normalizeHeading($heading): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $heading));
    }

    /** Every normalised heading that means this field (key, label, aliases). */
    public static function headingCandidates(string $key): array
    {
        $names = array_merge([$key, self::labelFor($key) ?? ''], self::ALIASES[$key] ?? []);

        return array_values(array_unique(array_filter(array_map([self::class, 'normalizeHeading'], $names))));
    }

    /** date-cast columns — need Excel-date-aware parsing on the way back in. */
    public const DATE_KEYS = ['date_of_birth', 'date_of_admission'];

    public static function keys(): array
    {
        return array_column(self::FIELDS, 'key');
    }

    public static function labelFor(string $key): ?string
    {
        foreach (self::FIELDS as $field) {
            if ($field['key'] === $key) {
                return $field['label'];
            }
        }

        return null;
    }

    /**
     * Filters+orders a request's chosen keys down to the real catalog,
     * in catalog order (not request order), dropping anything unknown.
     */
    public static function selected(array $requestedKeys): array
    {
        $requestedKeys = array_map('strval', $requestedKeys);

        return array_values(array_filter(
            self::FIELDS,
            fn($field) => in_array($field['key'], $requestedKeys, true)
        ));
    }

    /** Fields grouped for the checkbox UI, in catalog order within each group. */
    public static function grouped(): array
    {
        $groups = [];
        foreach (self::FIELDS as $field) {
            $groups[$field['group']][] = $field;
        }

        return $groups;
    }
}
