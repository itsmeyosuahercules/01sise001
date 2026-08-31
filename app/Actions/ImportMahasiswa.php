<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ImportMahasiswa
{
    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function importPath(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("File CSV tidak bisa dibaca: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("File CSV kosong atau gagal dibaca: {$path}");
        }

        return $this->importText($contents);
    }

    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function importText(string $contents): array
    {
        $contents = $this->stripBom($contents);
        $rows = $this->parseRows($contents);

        $created = 0;
        $skipped = 0;
        $errors = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 1;
            $nim = $this->normalizeNim($row['nim'] ?? '');
            $nama = $this->normalizeNama($row['nama'] ?? '');

            if ($nim === '' && $nama === '') {
                $skipped++;

                continue;
            }

            if ($nim === '' || $nama === '') {
                $errors[] = "Baris {$lineNumber}: NIM dan nama wajib diisi.";
                $skipped++;

                continue;
            }

            if (isset($seen[$nim])) {
                $errors[] = "Baris {$lineNumber}: NIM {$nim} duplikat di berkas yang sama.";
                $skipped++;

                continue;
            }

            $seen[$nim] = true;

            if (User::query()->where('nim', $nim)->exists()) {
                $skipped++;

                continue;
            }

            User::query()->create([
                'nim' => $nim,
                'name' => $nama,
                'email' => User::emailFromNim($nim),
                'password' => Hash::make(config('kelas.default_password')),
                'role' => UserRole::Anggota,
                'is_placeholder' => false,
            ]);

            $created++;
        }

        return [
            'created' => $created,
            'updated' => 0,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @return list<array{nim: string, nama: string}>
     */
    private function parseRows(string $contents): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];
        $lines = array_values(array_filter($lines, fn (string $line): bool => trim($line) !== ''));

        if ($lines === []) {
            return [];
        }

        $firstCells = str_getcsv($this->normalizeDelimiter($lines[0]));
        $hasHeader = $this->looksLikeHeader($firstCells);
        $dataLines = $hasHeader ? array_slice($lines, 1) : $lines;
        $headerMap = $hasHeader ? $this->headerMap($firstCells) : ['nim' => 0, 'nama' => 1];

        return Collection::make($dataLines)
            ->map(function (string $line) use ($headerMap): array {
                $cells = str_getcsv($this->normalizeDelimiter($line));

                return [
                    'nim' => (string) ($cells[$headerMap['nim']] ?? ''),
                    'nama' => (string) ($cells[$headerMap['nama']] ?? ''),
                ];
            })
            ->all();
    }

    /**
     * @param  list<string>  $cells
     */
    private function looksLikeHeader(array $cells): bool
    {
        $normalized = array_map(fn (string $cell): string => Str::of($cell)->trim()->lower()->toString(), $cells);

        return in_array('nim', $normalized, true) && (in_array('nama', $normalized, true) || in_array('name', $normalized, true));
    }

    /**
     * @param  list<string>  $cells
     * @return array{nim: int, nama: int}
     */
    private function headerMap(array $cells): array
    {
        $map = ['nim' => 0, 'nama' => 1];

        foreach ($cells as $index => $cell) {
            $key = Str::of((string) $cell)->trim()->lower()->toString();

            if ($key === 'nim') {
                $map['nim'] = $index;
            }

            if (in_array($key, ['nama', 'name', 'nama lengkap', 'nama_lengkap'], true)) {
                $map['nama'] = $index;
            }
        }

        return $map;
    }

    private function normalizeDelimiter(string $line): string
    {
        if (substr_count($line, ';') > substr_count($line, ',') && ! str_contains($line, ',')) {
            return str_replace(';', ',', $line);
        }

        return $line;
    }

    private function normalizeNim(string $nim): string
    {
        return (string) preg_replace('/\s+/', '', trim($nim));
    }

    private function normalizeNama(string $nama): string
    {
        return trim(preg_replace('/\s+/', ' ', $nama) ?? '');
    }

    private function stripBom(string $contents): string
    {
        return str_starts_with($contents, "\xEF\xBB\xBF")
            ? substr($contents, 3)
            : $contents;
    }
}
