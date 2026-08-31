<?php

namespace App\Enums;

enum LecturerQuestionStatus: string
{
    case Baru = 'baru';
    case Dipilih = 'dipilih';
    case Ditahan = 'ditahan';
    case Terkirim = 'terkirim';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Dipilih => 'Masuk paket',
            self::Ditahan => 'Ditahan',
            self::Terkirim => 'Sudah dikirim',
        };
    }
}
