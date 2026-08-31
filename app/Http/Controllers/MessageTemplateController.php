<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageTemplateRequest;
use App\Models\MessageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MessageTemplateController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MessageTemplate::class);

        $templates = MessageTemplate::query()
            ->with('author')
            ->orderBy('title')
            ->orderBy('id')
            ->get();

        return view('message-templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MessageTemplate::class);

        return view('message-templates.create');
    }

    public function store(StoreMessageTemplateRequest $request): RedirectResponse
    {
        MessageTemplate::query()->create([
            'user_id' => $request->user()->id,
            'title' => $request->string('title')->toString(),
            'slug' => Str::slug($request->string('title')->toString()).'-'.Str::lower(Str::random(6)),
            'body' => $request->string('body')->toString(),
        ]);

        return redirect()
            ->route('message-templates.index')
            ->with('status', 'Template disimpan.');
    }

    public function destroy(MessageTemplate $messageTemplate): RedirectResponse
    {
        $this->authorize('delete', $messageTemplate);

        $messageTemplate->delete();

        return back()->with('status', 'Template dihapus.');
    }
}
