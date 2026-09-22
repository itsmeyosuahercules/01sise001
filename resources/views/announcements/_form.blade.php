@php
    $announcement = $announcement ?? null;
@endphp

<div>
    <label class="block text-sm font-medium">Judul</label>
    <input type="text" name="title" value="{{ old('title', $announcement?->title) }}" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">
    @error('title') <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Kategori</label>
    <select name="category" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
        @foreach ($categories as $category)
            <option value="{{ $category->value }}" @selected(old('category', $announcement?->category?->value) === $category->value)>{{ $category->label() }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium">Isi</label>
    <div class="mt-1.5 flex flex-wrap gap-1.5" data-format-bar>
        <button type="button" data-format="bold" class="min-h-11 rounded-lg border border-line bg-paper px-3 text-sm font-semibold">Tebal</button>
        <button type="button" data-format="italic" class="min-h-11 rounded-lg border border-line bg-paper px-3 text-sm italic">Miring</button>
        <button type="button" data-format="link" class="min-h-11 rounded-lg border border-line bg-paper px-3 text-sm">Tautan</button>
        <button type="button" data-format="list" class="min-h-11 rounded-lg border border-line bg-paper px-3 text-sm">Daftar</button>
    </div>
    <textarea name="body" rows="10" required data-format-input class="mt-2 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-base leading-relaxed outline-none focus:border-navy sm:text-sm">{{ old('body', $announcement?->body) }}</textarea>
    <p class="mt-1 text-xs text-ink/50">Pilih kata, lalu ketuk Tebal atau Miring. Untuk tautan, pilih kata lalu ketuk Tautan. Tulisan biasa tetap tampil seperti biasa.</p>
    @error('body') <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Gambar atau file</label>
    <input
        type="file"
        name="attachments[]"
        multiple
        accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,image/*"
        class="mt-1.5 block w-full max-w-full text-sm file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-navy file:px-3 file:py-2 file:text-white"
    >
    <p class="mt-1 text-xs text-ink/50">Maksimal 8 file, 8 MB per file. Foto, PDF, dokumen, atau ZIP.</p>
    @error('attachments')
        <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
    @enderror
    @error('attachments.*')
        <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
    @enderror
</div>
<div>
    <label class="block text-sm font-medium">Berlaku sampai <span class="font-normal text-ink/45">(boleh dikosongkan)</span></label>
    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $announcement?->expires_at?->format('Y-m-d\TH:i')) }}" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement?->is_pinned))>
    Pin di atas
</label>
