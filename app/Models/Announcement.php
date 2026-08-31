<?php

namespace App\Models;

use App\Enums\AnnouncementCategory;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'title', 'body', 'category', 'is_pinned', 'published_at', 'expires_at'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Announcement $announcement): void {
            if (filled($announcement->slug)) {
                return;
            }

            $announcement->slug = static::uniqueSlugFrom((string) $announcement->title);
        });

        static::updating(function (Announcement $announcement): void {
            if (! $announcement->isDirty('title')) {
                return;
            }

            $announcement->slug = static::uniqueSlugFrom((string) $announcement->title, $announcement->id);
        });

        static::deleting(function (Announcement $announcement): void {
            $announcement->attachments()->get()->each(function (AnnouncementAttachment $attachment): void {
                $attachment->delete();
            });
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function uniqueSlugFrom(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'pengumuman';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => AnnouncementCategory::class,
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AnnouncementComment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(AnnouncementLike::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class)->orderBy('id');
    }

    /**
     * @param  Builder<Announcement>  $query
     * @return Builder<Announcement>
     */
    #[Scope]
    protected function visible(Builder $query): Builder
    {
        return $query
            ->where('published_at', '<=', now())
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function markReadBy(User $user): AnnouncementRead
    {
        return $this->reads()->firstOrCreate(
            ['user_id' => $user->id],
            ['read_at' => now()],
        );
    }

    public function isReadBy(User $user): bool
    {
        if ($this->relationLoaded('reads')) {
            return $this->reads->contains(fn (AnnouncementRead $read): bool => $read->user_id === $user->id);
        }

        return $this->reads()->whereBelongsTo($user)->exists();
    }

    public function isLikedBy(User $user): bool
    {
        if ($this->relationLoaded('likes')) {
            return $this->likes->contains(fn (AnnouncementLike $like): bool => $like->user_id === $user->id);
        }

        return $this->likes()->whereBelongsTo($user)->exists();
    }

    public function whatsappText(): string
    {
        $publishedAt = $this->published_at ?? $this->created_at;
        $date = $publishedAt instanceof Carbon
            ? $publishedAt->timezone(config('app.timezone'))->translatedFormat('d M Y H:i')
            : '';

        $attachments = $this->relationLoaded('attachments')
            ? $this->attachments
            : $this->attachments()->get();

        $attachmentLine = $attachments->isNotEmpty()
            ? 'Lampiran: '.$attachments->pluck('original_name')->implode(', ')
            : null;

        return implode("\n", array_filter([
            '['.config('kelas.name').' · '.$this->category->label().']',
            '*'.$this->title.'*',
            '',
            $this->body,
            $attachmentLine,
            '',
            '— '.($this->author?->name ?? 'KM').' · '.$date,
        ], fn (?string $line): bool => $line !== null));
    }
}
