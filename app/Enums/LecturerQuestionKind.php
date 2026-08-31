<?php

namespace App\Enums;

enum LecturerQuestionKind: string
{
    case Pertanyaan = 'pertanyaan';
    case Keluhan = 'keluhan';
    case Klarifikasi = 'klarifikasi';

    public function label(): string
    {
        return match ($this) {
            self::Pertanyaan => 'Pertanyaan',
            self::Keluhan => 'Keluhan',
            self::Klarifikasi => 'Klarifikasi',
        };
    }
}
