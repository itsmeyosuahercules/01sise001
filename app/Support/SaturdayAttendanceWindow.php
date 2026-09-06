<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class SaturdayAttendanceWindow
{
    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now((string) config('app.timezone'));
    }

    public static function isOpen(?CarbonImmutable $now = null): bool
    {
        $now ??= static::now();

        if (! $now->isSaturday()) {
            return false;
        }

        $config = config('kelas.attendance');
        $from = $now->setTimeFromTimeString((string) $config['open_from']);
        $until = $now->setTimeFromTimeString((string) $config['open_until']);

        return $now->between($from, $until);
    }

    public static function openWindowLabel(): string
    {
        $config = config('kelas.attendance');

        return str_replace(':', '.', (string) $config['open_from'])
            .'–'.str_replace(':', '.', (string) $config['open_until']).' WIB';
    }

    public static function currentOrLatestSaturday(?CarbonImmutable $now = null): CarbonImmutable
    {
        $now ??= static::now();

        if ($now->isSaturday()) {
            return $now->startOfDay();
        }

        return $now->previous(CarbonImmutable::SATURDAY)->startOfDay();
    }

    /**
     * @return list<CarbonImmutable>
     */
    public static function recentSaturdays(?int $weeks = null, ?CarbonImmutable $now = null): array
    {
        $weeks ??= max(1, (int) config('kelas.attendance.history_weeks', 20));
        $cursor = static::currentOrLatestSaturday($now);
        $dates = [];

        for ($i = 0; $i < $weeks; $i++) {
            $dates[] = $cursor;
            $cursor = $cursor->subWeek();
        }

        return $dates;
    }

    public static function resolveDate(?string $date): CarbonImmutable
    {
        if (! is_string($date) || $date === '') {
            return static::currentOrLatestSaturday();
        }

        $parsed = CarbonImmutable::parse($date, (string) config('app.timezone'))->startOfDay();

        if (! $parsed->isSaturday()) {
            throw new InvalidArgumentException('Tanggal rekap harus hari Sabtu.');
        }

        return $parsed;
    }
}
