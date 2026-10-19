<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NoteController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a paginated listing of notes with filters and search.
     * Enforces ownership isolation: employees only see their own notes.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $filters = [
            'search' => $request->input('search'),
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'due_status' => $request->input('due_status'),
            'due_from' => $request->input('due_from'),
            'due_to' => $request->input('due_to'),
            'user_id' => $user->isOwner() ? $request->input('user_id') : null,
        ];

        // Base query scoped by user authorization
        $baseQuery = Note::forUser($user, $filters['user_id']);

        // Stats calculation for the summary cards
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->open()->count(),
            'completed' => (clone $baseQuery)->completed()->count(),
            'cancelled' => (clone $baseQuery)->cancelled()->count(),
            'open_amount' => (float) ((clone $baseQuery)->open()->sum('amount') ?? 0),
            'overdue' => (clone $baseQuery)->open()->whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];

        // Filtered notes collection
        $notes = (clone $baseQuery)
            ->with('user')
            ->filter($filters)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $predefinedTitles = Note::PREDEFINED_TITLES;
        $users = $user->isOwner() ? User::orderBy('name')->get(['id', 'name']) : collect();

        return view('notes.index', compact('notes', 'stats', 'predefinedTitles', 'users', 'filters'));
    }

    /**
     * Show the form for creating a new note.
     */
    public function create(): View
    {
        $predefinedTitles = Note::PREDEFINED_TITLES;

        return view('notes.create', compact('predefinedTitles'));
    }

    /**
     * Store a newly created note.
     * Guaranteed server-side timestamp and non-spoofed authenticated creator.
     */
    public function store(NoteRequest $request): RedirectResponse
    {
        $resolvedTitle = $request->getResolvedTitle();

        $note = new Note();
        $note->user_id = Auth::id(); // Strict: creator cannot be spoofed
        $note->title = $resolvedTitle;
        $note->content = $request->input('content');
        $note->amount = $request->filled('amount') ? $request->input('amount') : null;
        $note->due_at = $request->filled('due_at') ? Carbon::parse($request->input('due_at')) : null;
        $note->status = $request->input('status', Note::STATUS_OPEN) ?: Note::STATUS_OPEN;
        $note->save();

        ActivityLogService::log(
            action: 'note_created',
            subject: $note,
            description: "تم تسجيل ملاحظة جديدة بعنوان [{$note->title}] بواسطة " . Auth::user()->name,
            newValues: [
                'title' => $note->title,
                'amount' => $note->amount,
                'due_at' => $note->due_at?->toDateTimeString(),
                'status' => $note->status,
            ]
        );

        return redirect()->route('notes.index')
                         ->with('success', 'تم حفظ الملاحظة بنجاح.');
    }

    /**
     * Display the specified note details.
     */
    public function show(Note $note): View
    {
        $this->authorize('view', $note);

        $note->load('user');

        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(Note $note): View
    {
        $this->authorize('update', $note);

        $predefinedTitles = Note::PREDEFINED_TITLES;

        return view('notes.edit', compact('note', 'predefinedTitles'));
    }

    /**
     * Update the specified note in storage.
     */
    public function update(NoteRequest $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $oldValues = [
            'title' => $note->title,
            'content' => $note->content,
            'amount' => $note->amount,
            'due_at' => $note->due_at?->toDateTimeString(),
            'status' => $note->status,
        ];

        $note->title = $request->getResolvedTitle();
        $note->content = $request->input('content');
        $note->amount = $request->filled('amount') ? $request->input('amount') : null;
        $note->due_at = $request->filled('due_at') ? Carbon::parse($request->input('due_at')) : null;

        if ($request->filled('status')) {
            $note->status = $request->input('status');
        }

        $note->save();

        ActivityLogService::log(
            action: 'note_updated',
            subject: $note,
            description: "تم تعديل بيانات الملاحظة [{$note->title}] بواسطة " . Auth::user()->name,
            oldValues: $oldValues,
            newValues: [
                'title' => $note->title,
                'amount' => $note->amount,
                'due_at' => $note->due_at?->toDateTimeString(),
                'status' => $note->status,
            ]
        );

        return redirect()->route('notes.index')
                         ->with('success', 'تم تحديث الملاحظة بنجاح.');
    }

    /**
     * Mark note as completed.
     */
    public function complete(Note $note): RedirectResponse
    {
        $this->authorize('complete', $note);

        if ($note->isCompleted()) {
            return redirect()->back()->with('info', 'هذه الملاحظة محددة كمكتملة بالفعل.');
        }

        $oldStatus = $note->status;
        $note->status = Note::STATUS_COMPLETED;
        $note->save();

        ActivityLogService::log(
            action: 'note_completed',
            subject: $note,
            description: "تم تحديد الملاحظة [{$note->title}] كمكتملة بواسطة " . Auth::user()->name,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => Note::STATUS_COMPLETED]
        );

        return redirect()->back()->with('success', 'تم تحديد الملاحظة كمكتملة بنجاح.');
    }

    /**
     * Mark note as cancelled.
     */
    public function cancel(Note $note): RedirectResponse
    {
        $this->authorize('cancel', $note);

        if ($note->isCancelled()) {
            return redirect()->back()->with('info', 'هذه الملاحظة ملغاة بالفعل.');
        }

        $oldStatus = $note->status;
        $note->status = Note::STATUS_CANCELLED;
        $note->save();

        ActivityLogService::log(
            action: 'note_cancelled',
            subject: $note,
            description: "تم إلغاء الملاحظة [{$note->title}] بواسطة " . Auth::user()->name,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => Note::STATUS_CANCELLED]
        );

        return redirect()->back()->with('success', 'تم إلغاء الملاحظة بنجاح.');
    }

    /**
     * Safely soft-delete/archive the note without permanently removing records.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $noteTitle = $note->title;
        $note->delete();

        ActivityLogService::log(
            action: 'note_cancelled',
            subject: $note,
            description: "تم أرشفة الملاحظة [{$noteTitle}] وحذفها آمناً بواسطة " . Auth::user()->name
        );

        return redirect()->route('notes.index')
                         ->with('success', 'تم نقل الملاحظة إلى الأرشيف بنجاح.');
    }
}
