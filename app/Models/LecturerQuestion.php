<?php

namespace App\Models;

use App\Enums\LecturerQuestionKind;
use App\Enums\LecturerQuestionStatus;
use Database\Factories\LecturerQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable(['user_id', 'kind', 'topic', 'body', 'hide_name', 'status', 'curator_id', 'curated_at', 'sent_at'])]
class LecturerQuestion extends Model
{
    /** @use HasFactory<LecturerQuestionFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'baru',
        'hide_name' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => LecturerQuestionKind::class,
            'status' => LecturerQuestionStatus::class,
            'hide_name' => 'boolean',
            'curated_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function curator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'curator_id');
    }

    public function whatsappLine(): string
    {
        $who = $this->hide_name
            ? 'Mahasiswa (nama disembunyikan)'
            : trim(($this->author?->name ?? 'Mahasiswa').' · '.($this->author?->nim ?? ''));

        return implode("\n", [
            $this->kind->label().' · '.$this->topic,
            $who,
            $this->body,
        ]);
    }

    /**
     * @param  iterable<int, LecturerQuestion>  $questions
     */
    public static function whatsappPackage(iterable $questions): string
    {
        $items = Collection::make($questions)->values();

        if ($items->isEmpty()) {
            return '['.config('kelas.name').' · Pertanyaan kelas]'."\n".'Belum ada yang dipilih.';
        }

        $lines = [
            '['.config('kelas.name').' · Pertanyaan kelas]',
            'Dikurasi KM. Bukan tugas Mentari — mohon ditanggapi dosen yang bersangkutan.',
            '',
        ];

        foreach ($items as $index => $question) {
            $lines[] = ($index + 1).'. '.$question->whatsappLine();
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }
}
