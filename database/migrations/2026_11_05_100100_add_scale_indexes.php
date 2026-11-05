<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for running thousands of schools on one database.
 *
 *  1. A curated list of the columns the hot queries filter on (parent login by
 *     phone, a student's marks, unread notification counts, date-range reports).
 *  2. A safety sweep: every table that has a school_id column but no index
 *     starting with school_id gets one, because almost every query in the app
 *     is "this school's rows".
 *
 * Safe to re-run: an index is only added when no existing index already
 * starts with the same columns, and a table or column that does not exist is
 * skipped. Every index this creates is named smasa_idx_* so down() removes
 * exactly those and nothing else.
 *
 * On a large production database run it in a quiet period: MySQL builds an
 * index without blocking reads/writes, but it still takes time and disk I/O.
 */
return new class extends Migration
{
    private const PREFIX = 'smasa_idx_';

    /** [table, [columns]] */
    private array $curated = [
        // Parent portal login + "my children": Student::where('primary_contact', phone)
        ['students', ['primary_contact']],
        ['parent_accounts', ['phone']],

        // A student's marks across exams (parent portal, report cards); marks by school + exam
        ['examination_marks', ['student_id']],
        ['examination_marks', ['school_id', 'examination_id']],

        // Finance reports and date-range filters
        ['fee_payments', ['school_id', 'payment_date']],
        ['student_fee_allocations', ['school_id', 'academic_year', 'term']],
        ['finance_transactions', ['school_id', 'account_id', 'academic_year', 'term']],

        // Notification bell: unread count per user, and a school's recent notifications
        ['smasa_notification_recipients', ['recipient_type', 'recipient_id', 'is_read']],
        ['smasa_notifications', ['school_id', 'created_at']],

        // National exams (Grading dashboard)
        ['exams', ['school_id', 'exam_type', 'academic_year']],
        ['student_exam_results', ['exam_id', 'student_id']],
        ['student_exam_results', ['student_id']],
    ];

    public function up(): void
    {
        foreach ($this->curated as [$table, $columns]) {
            $this->addIfMissing($table, $columns);
        }

        // Safety sweep: school_id must lead an index on every school-owned table.
        foreach (Schema::getTables() as $t) {
            $table = $t['name'] ?? null;

            if (!$table || $table === 'migrations' || !Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            $this->addIfMissing($table, ['school_id']);
        }
    }

    public function down(): void
    {
        foreach (Schema::getTables() as $t) {
            $table = $t['name'] ?? null;

            if (!$table) {
                continue;
            }

            foreach (Schema::getIndexes($table) as $index) {
                if (str_starts_with($index['name'], self::PREFIX)) {
                    try {
                        Schema::table($table, fn(Blueprint $b) => $b->dropIndex($index['name']));
                    } catch (\Throwable $e) {
                        Log::warning("Could not drop {$index['name']} on {$table}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function addIfMissing(string $table, array $columns): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumns($table, $columns)) {
            return;
        }

        foreach (Schema::getIndexes($table) as $index) {
            $existing = array_map('strtolower', $index['columns']);

            // Already covered: an index (or primary/unique key) starts with these columns.
            if (array_slice($existing, 0, count($columns)) === array_map('strtolower', $columns)) {
                return;
            }
        }

        $name = self::PREFIX . substr(md5($table . '|' . implode(',', $columns)), 0, 8) . '_' . substr($table, 0, 30);

        try {
            Schema::table($table, fn(Blueprint $b) => $b->index($columns, $name));
        } catch (\Throwable $e) {
            // One bad table must not stop the rest (e.g. a column type that cannot be indexed).
            Log::warning("Index {$name} on {$table} skipped: " . $e->getMessage());
        }
    }
};
