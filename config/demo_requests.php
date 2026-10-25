<?php

/*
| Homepage "Request a Demo" form.
|
|  DEMO_REQUEST_NOTIFY_TO   who is emailed the moment a request arrives (comma separated for several)
|  DEMO_REQUEST_NOTIFY_CC   optional extra recipients (comma separated)
|
| Every request is ALWAYS saved to the demo_requests table first, so nothing is lost
| even if the mail server is down. The admin inbox lives at /admin/demo-requests.
*/
return [
    'notify_to' => array_values(array_filter(array_map('trim', explode(',', env('DEMO_REQUEST_NOTIFY_TO', 'techsatetechnologies@gmail.com'))))),
    'notify_cc' => array_values(array_filter(array_map('trim', explode(',', env('DEMO_REQUEST_NOTIFY_CC', ''))))),

    // Send the requester a "we received your request" email.
    'send_acknowledgement' => (bool) env('DEMO_REQUEST_SEND_ACK', true),

    'school_types' => [
        'idaad_thanawi' => 'Idaad & Thanawi (Islamic Secondary)',
        'primary_theology' => 'Primary Theology',
        'primary_secular' => 'Primary Secular',
        'primary_both' => 'Both Primary Theology & Secular',
        'secondary' => 'Secondary',
    ],

    'student_ranges' => [
        '0-150' => '0 – 150',
        '151-300' => '151 – 300',
        '301-500' => '301 – 500',
        '501-800' => '501 – 800',
        '801-1200' => '801 – 1,200',
        '1201-1500' => '1,201 – 1,500',
        '1501-2000' => '1,501 – 2,000',
        '2001+' => '2,001+',
    ],

    'statuses' => [
        'new' => ['label' => 'New', 'color' => '#2563eb'],
        'contacted' => ['label' => 'Contacted', 'color' => '#d97706'],
        'demo_scheduled' => ['label' => 'Demo scheduled', 'color' => '#7c3aed'],
        'won' => ['label' => 'Won (school signed)', 'color' => '#16a34a'],
        'lost' => ['label' => 'Lost / not interested', 'color' => '#6b7280'],
    ],

    'per_page' => 20,
];
