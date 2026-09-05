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
        return ($now ?? static::now())->isSaturday();
    }

    public static function currentOrLatestSaturday(?CarbonImmutable $now = null): CarbonImmutable
    {
        $now ??= static::now();

        if ($now->isSaturday()) {
            return $now->startOfDay();
        }

        return $now->subDay()->previousOrSame(CarbonImmutable::SATURDAY)->startOfDay();
    }

    /**
     * @return list<CarbonImmutable>
     */
    public static function recentSaturdays(int $weeks = 12, ?CarbonImmutable $now = null): array
    {
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
