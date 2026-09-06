<?php

namespace App\Models;

use Database\Factories\SaturdayAttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'marked_by', 'attended_on', 'photo_path', 'latitude', 'longitude', 'accuracy', 'captured_at', 'note'])]
class SaturdayAttendance extends Model
{
    /** @use HasFactory<SaturdayAttendanceFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (SaturdayAttendance $attendance): void {
            if (filled($attendance->photo_path)) {
                Storage::disk('local')->delete($attendance->photo_path);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attended_on' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy' => 'integer',
            'captured_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function isManual(): bool
    {
        return $this->marked_by !== null;
    }

    public function hasPhoto(): bool
    {
        return filled($this->photo_path);
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function mapsUrl(): ?string
    {
        if (! $this->hasLocation()) {
            return null;
        }

        return 'https://maps.google.com/?q='.$this->latitude.','.$this->longitude;
    }

    public function coordinateLabel(): ?string
    {
        if (! $this->hasLocation()) {
            return null;
        }

        return number_format($this->latitude, 6).', '.number_format($this->longitude, 6);
    }

    public function photoDataUri(): ?string
    {
        if (! filled($this->photo_path) || ! Storage::disk('local')->exists($this->photo_path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($this->photo_path);

        if (! is_string($contents) || $contents === '') {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($this->photo_path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
