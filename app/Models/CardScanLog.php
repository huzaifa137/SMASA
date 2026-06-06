<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardScanLog extends Model
{
    protected $fillable = [
        'school_id',
        'card_number',
        'card_type',
        'scan_category',
        'scan_result',
        'result_message',
        'result_data',
        'scanned_by',
        'scanned_by_type',
        'device_info',
    ];

    protected $casts = [
        'result_data' => 'array',
    ];

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'attendance_arrival' => 'School Arrival',
            'attendance_class'   => 'Class Attendance',
            'library_issue'      => 'Library Issue Book',
            'library_return'     => 'Library Return Book',
            'library_reserve'    => 'Library Reserve',
            'finance_balance'    => 'Fee Balance Check',
            'finance_payment'    => 'Fee Payment',
            'info'               => 'Card Info / Verification',
            default              => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    public static function categoryIcon(string $category): string
    {
        return match ($category) {
            'attendance_arrival' => 'fa-school',
            'attendance_class'   => 'fa-chalkboard-teacher',
            'library_issue'      => 'fa-book-open',
            'library_return'     => 'fa-undo',
            'library_reserve'    => 'fa-bookmark',
            'finance_balance'    => 'fa-wallet',
            'finance_payment'    => 'fa-cash-register',
            'info'               => 'fa-id-card',
            default              => 'fa-qrcode',
        };
    }

    public static function categoryColor(string $category): string
    {
        return match ($category) {
            'attendance_arrival' => '#2f2ccb',
            'attendance_class'   => '#7c3aed',
            'library_issue'      => '#059669',
            'library_return'     => '#0891b2',
            'library_reserve'    => '#d97706',
            'finance_balance'    => '#1d4ed8',
            'finance_payment'    => '#15803d',
            'info'               => '#64748b',
            default              => '#334155',
        };
    }
}