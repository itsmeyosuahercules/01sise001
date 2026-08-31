<?php

namespace App\Enums;

enum AnnouncementCategory: string
{
    case Jadwal = 'jadwal';
    case Tugas = 'tugas';
    case Ujian = 'ujian';
    case Ruangan = 'ruangan';
    case Seminar = 'seminar';
    case Prodi = 'prodi';
    case Umum = 'umum';

    public function label(): string
    {
        return match ($this) {
            self::Jadwal => 'Jadwal',
            self::Tugas => 'Tugas',
            self::Ujian => 'Ujian',
            self::Ruangan => 'Ruangan',
            self::Seminar => 'Seminar',
            self::Prodi => 'Prodi',
            self::Umum => 'Umum',
        };
    }
}
