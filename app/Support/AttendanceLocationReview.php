<?php

namespace App\Support;

use App\Models\SaturdayAttendance;
use Illuminate\Support\Collection;

class AttendanceLocationReview
{
    /**
     * @param  Collection<int, SaturdayAttendance>  $sameDay
     * @return list<string>
     */
    public static function warnings(SaturdayAttendance $attendance, Collection $sameDay): array
    {
        if (! $attendance->hasLocation()) {
            return [];
        }

        $warnings = [];
        $distance = static::distanceFromCampus($attendance);

        if ($distance !== null && $distance > (int) config('kelas.attendance.campus_radius_meters', 800)) {
            $warnings[] = 'Jauh dari kampus ('.number_format($distance / 1000, 1).' km)';
        }

        if ($attendance->accuracy === 0) {
            $warnings[] = 'Lokasi terlihat tidak asli';
        }

        $pin = static::pin($attendance);
        $sharesPin = $sameDay->contains(function (SaturdayAttendance $other) use ($attendance, $pin): bool {
            return $other->isNot($attendance)
                && $other->hasLocation()
                && static::pin($other) === $pin;
        });

        if ($sharesPin) {
            $warnings[] = 'Lokasi sama dengan teman sekelas';
        }

        return $warnings;
    }

    public static function distanceFromCampus(SaturdayAttendance $attendance): ?int
    {
        $latitude = config('kelas.attendance.campus_latitude');
        $longitude = config('kelas.attendance.campus_longitude');

        if (! is_numeric($latitude) || ! is_numeric($longitude) || ! $attendance->hasLocation()) {
            return null;
        }

        return static::meters(
            (float) $latitude,
            (float) $longitude,
            (float) $attendance->latitude,
            (float) $attendance->longitude,
        );
    }

    public static function meters(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): int
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($toLatitude - $fromLatitude);
        $longitudeDelta = deg2rad($toLongitude - $fromLongitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($longitudeDelta / 2) ** 2;

        return (int) round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private static function pin(SaturdayAttendance $attendance): string
    {
        return number_format((float) $attendance->latitude, 5).','.number_format((float) $attendance->longitude, 5);
    }
}
