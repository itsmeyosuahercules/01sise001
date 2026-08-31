<?php

namespace App\Console\Commands;

use App\Actions\PrepareProduction;
use Illuminate\Console\Command;
use RuntimeException;

class PrepareProductionCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'kelas:siapkan-produksi
        {--csv= : Path CSV nim,nama}
        {--km= : NIM yang jadi KM}';

    /**
     * @var string
     */
    protected $description = 'Hapus akun uji, impor roster asli, dan tetapkan KM';

    public function handle(PrepareProduction $prepare): int
    {
        $csv = $this->option('csv');
        $km = $this->option('km');

        try {
            $result = $prepare->handle(
                is_string($csv) && $csv !== '' ? $csv : null,
                is_string($km) && $km !== '' ? $km : null,
            );
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Akun uji dihapus: {$result['purged']}");
        $this->info("Mahasiswa dibuat: {$result['created']}");
        $this->info("Dilewati: {$result['skipped']}");

        if ($result['km'] !== null) {
            $this->info("KM: {$result['km']}");
        } else {
            $this->warn('Belum ada KM. Jalankan lagi dengan --km=NIM atau set KELAS_KM_NIM.');
        }

        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
