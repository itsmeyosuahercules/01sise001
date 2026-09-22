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
    <textarea name="body" rows="8" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">{{ old('body', $announcement?->body) }}</textarea>
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
