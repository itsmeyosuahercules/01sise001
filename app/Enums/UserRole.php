<?php

namespace App\Enums;

enum UserRole: string
{
    case Km = 'km';
    case Anggota = 'anggota';

    public function label(): string
    {
        return match ($this) {
            self::Km => 'KM',
            self::Anggota => 'Anggota',
        };
    }

    public function isKm(): bool
    {
        return $this === self::Km;
    }

    public function isPengurus(): bool
    {
        return $this->isKm();
    }

    public function canPublishAnnouncements(): bool
    {
        return $this->isKm();
    }

    public function canManageRoles(): bool
    {
        return $this->isKm();
    }

    public function canResetPasswords(): bool
    {
        return $this->isKm();
    }

    public function canImportMahasiswa(): bool
    {
        return $this->isKm();
    }

    public function canReviewAbsences(): bool
    {
        return $this->isKm();
    }

    public function canManageTemplates(): bool
    {
        return $this->isKm();
    }

    public function canCurateLecturerQuestions(): bool
    {
        return $this->isKm();
    }
}
