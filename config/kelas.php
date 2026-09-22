<?php

return [
    'name' => env('KELAS_NAME', '01SISE001'),
    'email_domain' => env('KELAS_EMAIL_DOMAIN', '01sise001.test'),
    'default_password' => env('KELAS_DEFAULT_PASSWORD', '01SISE001'),
    'mahasiswa_csv' => env('KELAS_MAHASISWA_CSV', database_path('data/mahasiswa.csv')),
    'km_nim' => env('KELAS_KM_NIM'),
    'announcement_attachments' => [
        'max_files' => 8,
        'max_kilobytes' => 8192,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'],
    ],
    'avatar' => [
        'max_kilobytes' => 2048,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
    'attendance' => [
        'max_kilobytes' => 4096,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'open_from' => env('KELAS_ATTENDANCE_OPEN_FROM', '06:00'),
        'open_until' => env('KELAS_ATTENDANCE_OPEN_UNTIL', '18:00'),
        'history_weeks' => (int) env('KELAS_ATTENDANCE_HISTORY_WEEKS', 20),
        'campus_latitude' => env('KELAS_CAMPUS_LATITUDE', -6.122778),
        'campus_longitude' => env('KELAS_CAMPUS_LONGITUDE', 106.205556),
        'campus_radius_meters' => (int) env('KELAS_CAMPUS_RADIUS', 800),
    ],
];
