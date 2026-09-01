<?php

namespace App\Enums;

enum UserRole: string
{
    case Km = 'km';
    case Wakil = 'wakil';
    case Anggota = 'anggota';

    public function label(): string
    {
        return match ($this) {
            self::Km => 'KM',
            self::Wakil => 'Wakil',
            self::Anggota => 'Anggota',
        };
    }

    public function isKm(): bool
    {
        return $this === self::Km;
    }

    public function isWakil(): bool
    {
        return $this === self::Wakil;
    }

    public function isPengurus(): bool
    {
        return $this->isKm() || $this->isWakil();
    }

    public function canPublishAnnouncements(): bool
    {
        return $this->isPengurus();
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
        return $this->isPengurus();
    }

    public function canManageTemplates(): bool
    {
        return $this->isPengurus();
    }

    public function canCurateLecturerQuestions(): bool
    {
        return $this->isPengurus();
    }
}
