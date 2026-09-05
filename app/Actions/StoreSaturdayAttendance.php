<?php

namespace App\Actions;

use App\Models\SaturdayAttendance;
use App\Models\User;
use App\Support\SaturdayAttendanceWindow;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class StoreSaturdayAttendance
{
    /**
     * @param  array{latitude: float, longitude: float, accuracy?: int|null}  $location
     */
    public function handle(User $user, UploadedFile $photo, array $location): SaturdayAttendance
    {
        if (! SaturdayAttendanceWindow::isOpen()) {
            throw new RuntimeException('Hadir hanya bisa dikirim hari Sabtu.');
        }

        $attendedOn = SaturdayAttendanceWindow::currentOrLatestSaturday()->toDateString();
        $existing = SaturdayAttendance::query()
            ->whereBelongsTo($user)
            ->whereDate('attended_on', $attendedOn)
            ->first();

        if ($existing instanceof SaturdayAttendance && filled($existing->photo_path)) {
            Storage::disk('local')->delete($existing->photo_path);
        }

        $path = $photo->store('attendances/'.$attendedOn, 'local');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Foto hadir gagal disimpan.');
        }

        $payload = [
            'photo_path' => $path,
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'accuracy' => $location['accuracy'] ?? null,
            'captured_at' => now(),
        ];

        if ($existing instanceof SaturdayAttendance) {
            $existing->update($payload);

            return $existing->fresh() ?? $existing;
        }

        return SaturdayAttendance::query()->create([
            ...$payload,
            'user_id' => $user->id,
            'attended_on' => $attendedOn,
        ]);
    }
}
