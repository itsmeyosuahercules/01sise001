<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'nim', 'role', 'is_placeholder', 'avatar_path', 'bio'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_placeholder' => 'boolean',
        ];
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function announcementReads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function announcementComments(): HasMany
    {
        return $this->hasMany(AnnouncementComment::class);
    }

    public function announcementLikes(): HasMany
    {
        return $this->hasMany(AnnouncementLike::class);
    }

    public function absenceRequests(): HasMany
    {
        return $this->hasMany(AbsenceRequest::class);
    }

    public function saturdayAttendances(): HasMany
    {
        return $this->hasMany(SaturdayAttendance::class);
    }

    public function reviewedAbsenceRequests(): HasMany
    {
        return $this->hasMany(AbsenceRequest::class, 'reviewer_id');
    }

    public function lecturerQuestions(): HasMany
    {
        return $this->hasMany(LecturerQuestion::class);
    }

    public function curatedLecturerQuestions(): HasMany
    {
        return $this->hasMany(LecturerQuestion::class, 'curator_id');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    #[Scope]
    protected function real(Builder $query): Builder
    {
        return $query->where('is_placeholder', false);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    #[Scope]
    protected function placeholders(Builder $query): Builder
    {
        return $query->where('is_placeholder', true);
    }

    public static function emailFromNim(string $nim): string
    {
        return $nim.'@'.config('kelas.email_domain');
    }

    public function isPengurus(): bool
    {
        return $this->role->isPengurus();
    }

    public function hasAvatar(): bool
    {
        return filled($this->avatar_path);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $first = mb_substr($parts[0] ?? '?', 0, 1);
        $last = count($parts) > 1 ? mb_substr((string) $parts[array_key_last($parts)], 0, 1) : '';

        return mb_strtoupper($first.$last);
    }
}
