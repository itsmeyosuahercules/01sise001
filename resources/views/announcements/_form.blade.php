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
        class="mt-1.5 w-full text-sm file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-navy file:px-3 file:py-2 file:text-white"
    >
    <p class="mt-1 text-xs text-ink/50">Maksimal 8 berkas, 8 MB per berkas. Foto, PDF, Office, atau ZIP.</p>
    @error('attachments')
        <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
    @enderror
    @error('attachments.*')
        <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
    @enderror
</div>
@if ($announcement?->attachments?->isNotEmpty())
    <div class="space-y-2">
        <p class="text-sm font-medium">Lampiran sekarang</p>
        @foreach ($announcement->attachments as $attachment)
            <div class="flex items-center justify-between gap-3 rounded-xl border border-line bg-paper px-3 py-2 text-sm">
                <a href="{{ route('announcements.attachments.show', [$announcement, $attachment]) }}" class="min-w-0 truncate text-navy hover:underline">
                    {{ $attachment->original_name }}
                    <span class="text-ink/45">· {{ $attachment->humanSize() }}</span>
                </a>
                <form method="POST" action="{{ route('announcements.attachments.destroy', [$announcement, $attachment]) }}" onsubmit="return confirm('Hapus lampiran ini?')">
                    @csrf
                    @method('DELETE')
                    <x-btn variant="ghost" type="submit" class="!px-2 !py-1 text-xs">Hapus</x-btn>
                </form>
            </div>
        @endforeach
    </div>
@endif
<div>
    <label class="block text-sm font-medium">Berlaku sampai (opsional)</label>
    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $announcement?->expires_at?->format('Y-m-d\TH:i')) }}" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement?->is_pinned))>
    Pin di atas
</label>
