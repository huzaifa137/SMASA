<?php

/**
 * Sales Team module — tuning knobs.
 *
 * Money is read from the Billing module (billing_invoices / billing_payments),
 * so every figure on the Leaders Dashboard reconciles with Billing.
 */
return [

    // schools.school_status values (see School\all-schools).
    'school_statuses' => [
        10 => ['label' => 'Active', 'tone' => 'ok'],
        1 => ['label' => 'Pending activation', 'tone' => 'warn'],
        9 => ['label' => 'Suspended', 'tone' => 'bad'],
        8 => ['label' => 'Locked', 'tone' => 'bad'],
        0 => ['label' => 'Banned', 'tone' => 'bad'],
    ],
    'live_status' => 10,

    /*
    | Target metrics. `unit` drives formatting; `credit` documents (in the UI)
    | whose numbers they are.
    */
    'metrics' => [
        'schools_enrolled' => [
            'label' => 'Schools enrolled',
            'short' => 'Enrolled',
            'unit' => 'count',
            'icon' => 'fa-school',
            'help' => 'New schools registered by the representative in the period. Credit stays with whoever enrolled the school, even after a transfer.',
        ],
        'students_onboarded' => [
            'label' => 'Students onboarded',
            'short' => 'Students',
            'unit' => 'count',
            'icon' => 'fa-user-graduate',
            'help' => 'Students registered during the period in the schools the representative currently owns.',
        ],
        'amount_collected' => [
            'label' => 'Amount collected',
            'short' => 'Collected',
            'unit' => 'money',
            'icon' => 'fa-hand-holding-dollar',
            'help' => 'Valid payments dated in the period. A payment is credited to whoever owned the school on the payment date.',
        ],
        'collection_rate' => [
            'label' => 'Collection rate',
            'short' => 'Rate',
            'unit' => 'percent',
            'icon' => 'fa-percent',
            'help' => 'Collected ÷ expected on the invoices of the representative\'s current schools for the period (0–100).',
        ],
    ],

    // Pacing: how a running target is judged against the time elapsed.
    'pace' => [
        'on_track_ratio' => 0.90,   // progress >= 90% of where it should be
        'at_risk_ratio' => 0.60,    // >= 60%; below this is "behind"
    ],

    // Overall attainment = average of each target's % capped at this value.
    'attainment_cap' => 100,

    // Reasons offered when schools change hands.
    'transfer_reasons' => [
        'rep_left' => 'Representative left / unavailable',
        'territory' => 'Territory or region change',
        'workload' => 'Balancing workload',
        'performance' => 'Performance or follow-up concerns',
        'client_request' => 'Request from the school',
        'other' => 'Other (explain in the note)',
    ],

    // Estimated commission (only shown when a rep has a rate > 0).
    'default_commission_rate' => (float) env('SALES_DEFAULT_COMMISSION_RATE', 0),

    'per_page' => 25,
    'trend_months' => 6,
];
