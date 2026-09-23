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
        ['key' => 'guardian_names', 'label' => 'Guardian Name(s)', 'group' => 'Guardian'],
        ['key' => 'relation', 'label' => 'Guardian Relation', 'group' => 'Guardian'],
        ['key' => 'guardian_phone', 'label' => 'Guardian Phone', 'group' => 'Guardian'],
        ['key' => 'guardian_email', 'label' => 'Guardian Email', 'group' => 'Guardian'],
        ['key' => 'medical_history', 'label' => 'Medical History', 'group' => 'Other'],
        ['key' => 'comments', 'label' => 'Comments', 'group' => 'Other'],
    ];

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
