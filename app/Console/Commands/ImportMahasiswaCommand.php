<?php

namespace App\Console\Commands;

use App\Actions\ImportMahasiswa;
use Illuminate\Console\Command;
use RuntimeException;

class ImportMahasiswaCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'kelas:import-mahasiswa {path? : Path ke CSV nim,nama}';

    /**
     * @var string
     */
    protected $description = 'Impor atau perbarui mahasiswa dari CSV (upsert berdasarkan NIM)';

    public function handle(ImportMahasiswa $importer): int
    {
        $path = $this->argument('path') ?: config('kelas.mahasiswa_csv');

        try {
            $result = $importer->importPath($path);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Dibuat: {$result['created']}");
        $this->info("Dilewati: {$result['skipped']}");

        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
