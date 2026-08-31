<?php

namespace App\Http\Controllers;

use App\Actions\ImportMahasiswa;
use App\Http\Requests\ImportMahasiswaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MahasiswaImportController extends Controller
{
    public function create(): View
    {
        $this->authorize('import', User::class);

        return view('mahasiswa-imports.create');
    }

    public function store(ImportMahasiswaRequest $request, ImportMahasiswa $importer): RedirectResponse
    {
        $contents = $request->hasFile('csv')
            ? $request->file('csv')->getContent()
            : (string) $request->input('paste');

        $result = $importer->importText($contents);

        $message = "Impor selesai. Dibuat {$result['created']}, dilewati {$result['skipped']}.";

        if ($result['errors'] !== []) {
            return back()
                ->with('status', $message)
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('roster.index')->with('status', $message);
    }
}
