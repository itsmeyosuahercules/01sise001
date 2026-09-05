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
    ],
];
