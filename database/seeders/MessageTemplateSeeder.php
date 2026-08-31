<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::query()->where('role', UserRole::Km)->value('id');

        $templates = [
            [
                'slug' => 'izin-kolektif',
                'title' => 'Izin kolektif ke dosen',
                'body' => "Assalamu'alaikum Wr. Wb.\n\nYth. Bapak/Ibu Dosen,\n\nKami dari kelas {{kelas}} mohon izin tidak dapat mengikuti perkuliahan pada [tanggal] karena [alasan].\n\nAtas perhatian Bapak/Ibu, kami ucapkan terima kasih.\n\nHormat kami,\nKM {{kelas}}\n{{nama}} ({{nim}})",
            ],
            [
                'slug' => 'ganti-ruangan',
                'title' => 'Pengumuman ganti ruangan',
                'body' => "[INFO {{kelas}}]\nKuliah [mata kuliah] hari ini pindah ke ruangan [kode ruang].\nJam tetap [jam].\nMohon langsung ke ruangan baru, jangan ke ruang lama.",
            ],
            [
                'slug' => 'minta-data-prodi',
                'title' => 'Balasan data ke prodi',
                'body' => "Yth. Bapak/Ibu Prodi,\n\nBerikut data kelas {{kelas}} yang diminta:\n- Jumlah mahasiswa: [jumlah]\n- File terlampir / list menyusul.\n\nKM {{kelas}}\n{{nama}}",
            ],
            [
                'slug' => 'pengumuman-uts',
                'title' => 'Pengumuman UTS',
                'body' => "[PENGUMUMAN UTS {{kelas}}]\nMata kuliah: [nama MK]\nHari/tanggal: [tanggal]\nJam: [jam]\nRuangan/mode: [ruangan]\nBawa: [ketentuan]\n\nPin pesan ini. Yang belum baca, cek di papan kelas.",
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::query()->updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'user_id' => $authorId,
                    'title' => $template['title'],
                    'body' => $template['body'],
                ],
            );
        }
    }
}
