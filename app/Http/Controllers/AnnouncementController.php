<?php

namespace App\Http\Controllers;

use App\Actions\AttachAnnouncementFiles;
use App\Enums\AnnouncementCategory;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Announcement::class);

        $user = request()->user();

        $announcements = Announcement::query()
            ->visible()
            ->with([
                'author',
                'reads' => fn ($query) => $query->whereBelongsTo($user),
                'likes' => fn ($query) => $query->whereBelongsTo($user),
            ])
            ->withCount(['reads', 'likes', 'comments', 'attachments'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('announcements.create', [
            'categories' => AnnouncementCategory::cases(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request, AttachAnnouncementFiles $attachFiles): RedirectResponse
    {
        $announcement = DB::transaction(function () use ($request, $attachFiles): Announcement {
            $announcement = Announcement::query()->create([
                ...$request->safe()->only(['title', 'body', 'category', 'expires_at']),
                'is_pinned' => $request->boolean('is_pinned'),
                'user_id' => $request->user()->id,
                'published_at' => now(),
            ]);

            $attachFiles->attach($announcement, $this->uploadedAttachments($request));

            return $announcement;
        });

        return redirect()
            ->route('announcements.show', $announcement)
            ->with('status', 'Pengumuman dipublikasikan.');
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $this->authorize('view', $announcement);

        $announcement->markReadBy($request->user());

        $announcement->load([
            'author',
            'attachments',
            'reads.user',
            'likes' => fn ($query) => $query->with('user')->latest('id'),
            'comments' => fn ($query) => $query->with('author')->orderBy('created_at')->orderBy('id'),
        ])->loadCount(['likes', 'comments', 'reads']);

        return view('announcements.show', [
            'announcement' => $announcement,
        ]);
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        $announcement->load('attachments');

        return view('announcements.edit', [
            'announcement' => $announcement,
            'categories' => AnnouncementCategory::cases(),
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement, AttachAnnouncementFiles $attachFiles): RedirectResponse
    {
        DB::transaction(function () use ($request, $announcement, $attachFiles): void {
            $announcement->update([
                ...$request->safe()->only(['title', 'body', 'category', 'expires_at']),
                'is_pinned' => $request->boolean('is_pinned'),
            ]);

            $attachFiles->attach($announcement, $this->uploadedAttachments($request));
        });

        return redirect()
            ->route('announcements.show', $announcement)
            ->with('status', 'Pengumuman diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('status', 'Pengumuman dihapus.');
    }

    /**
     * @return list<UploadedFile>
     */
    private function uploadedAttachments(Request $request): array
    {
        $files = $request->file('attachments', []);

        if ($files instanceof UploadedFile) {
            return [$files];
        }

        return array_values(array_filter(
            $files,
            fn (mixed $file): bool => $file instanceof UploadedFile,
        ));
    }
}
