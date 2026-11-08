<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shared secret
    |--------------------------------------------------------------------------
    |
    | The remote panel must send this value in the "X-Webhook-Secret" header
    | (or as "Authorization: Bearer <secret>"). Requests without it are
    | rejected with 401. Leave empty only in local development.
    |
    */
    'secret' => env('ATTENDANCE_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Optional HMAC signing secret
    |--------------------------------------------------------------------------
    |
    | When set, the panel must also send "X-Webhook-Signature" containing
    | sha256=<hmac of the raw request body>. This protects against replayed or
    | tampered payloads. Leave empty to rely on the shared secret alone.
    |
    */
    'signing_secret' => env('ATTENDANCE_WEBHOOK_SIGNING_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Optional IP allow list
    |--------------------------------------------------------------------------
    |
    | Comma separated list of IPs allowed to call the webhook. Empty = any IP.
    |
    */
    'allowed_ips' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ATTENDANCE_WEBHOOK_IPS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Default device IP stored on synced rows
    |--------------------------------------------------------------------------
    |
    | Written to user_ip / user_ip_checkout when the payload carries no IP, so
    | webhook punches are distinguishable from in-browser punches.
    |
    */
    'device_ip' => env('ATTENDANCE_WEBHOOK_DEVICE_IP', '27.147.177.102'),

    /*
    |--------------------------------------------------------------------------
    | Fallback shift
    |--------------------------------------------------------------------------
    |
    | Used when neither the employee nor their branch has a shift configured.
    | Format: "start,end" in 24h time.
    |
    */
    'default_shift' => env('ATTENDANCE_WEBHOOK_DEFAULT_SHIFT', '09:00:00,18:00:00'),

    /*
    |--------------------------------------------------------------------------
    | Default required working minutes
    |--------------------------------------------------------------------------
    */
    'default_working_minutes' => (int) env('ATTENDANCE_WEBHOOK_WORKING_MINUTES', 540),

    /*
    |--------------------------------------------------------------------------
    | Max events accepted in one request
    |--------------------------------------------------------------------------
    */
    'max_events' => (int) env('ATTENDANCE_WEBHOOK_MAX_EVENTS', 500),

    /*
    |--------------------------------------------------------------------------
    | Lock completed records
    |--------------------------------------------------------------------------
    |
    | When a work date already has BOTH a check-in and a check-out stored, the
    | webhook leaves that row untouched and reports "unchanged" instead of
    | rewriting it. Set to false to go back to earliest-in / latest-out always
    | winning.
    |
    */
    'lock_completed_records' => (bool) env('ATTENDANCE_WEBHOOK_LOCK_COMPLETED', true),

    /*
    |--------------------------------------------------------------------------
    | Status codes written to employee_attendances.status
    |--------------------------------------------------------------------------
    |
    | These mirror AttendanceController's browser check-in / check-out flow so
    | webhook rows render identically in the existing reports.
    |
    */
    'status' => [
        'late'       => 1,
        'on_time'    => 3,
        'incomplete' => 4,
        'overtime'   => 5,
        'perfect'    => 6,
    ],
];
