<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\SiteNotification;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Display inbox with all message threads.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        $threads = $this->threadsFor($user)->get();
        $selectedThread = $threads->first();

        return view('messages.index', [
            'threads' => $threads->map(fn (MessageThread $thread) => $this->threadSummaryPayload($thread, $user))->values(),
            'selectedThreadId' => $selectedThread?->id,
        ]);
    }

    public function summary(): JsonResponse
    {
        $user = Auth::user();
        $threads = $this->threadsFor($user)->limit(6)->get();
        $unreadCount = Message::whereHas('thread', function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'threads' => $threads->map(fn (MessageThread $thread) => $this->threadSummaryPayload($thread, $user))->values(),
        ]);
    }

    /**
     * Show a specific conversation thread.
     */
    public function show(string $id): View
    {
        $user = Auth::user();
        $thread = MessageThread::with(['user1', 'user2'])->findOrFail($id);

        // Verify user is part of this thread
        if ($thread->user1_id !== $user->id && $thread->user2_id !== $user->id) {
            abort(403);
        }

        $threads = $this->threadsFor($user)->get();

        return view('messages.index', [
            'threads' => $threads->map(fn (MessageThread $thread) => $this->threadSummaryPayload($thread, $user))->values(),
            'selectedThreadId' => (int) $id,
        ]);
    }

    public function open(User $recipient): JsonResponse
    {
        $user = Auth::user();

        if ((int) $recipient->id === (int) $user->id) {
            return response()->json(['message' => 'You cannot message yourself.'], 422);
        }

        $thread = $this->findOrCreateThread($user, $recipient);

        return response()->json($this->threadPayload($thread->fresh(['user1', 'user2']), $user));
    }

    public function thread(MessageThread $thread): JsonResponse
    {
        $user = Auth::user();

        if ((int) $thread->user1_id !== (int) $user->id && (int) $thread->user2_id !== (int) $user->id) {
            abort(403);
        }

        return response()->json($this->threadPayload($thread->load(['user1', 'user2']), $user));
    }

    /**
     * Start a new conversation or get existing thread.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $recipientId = $request->get('user_id');

        if ($recipientId) {
            $recipient = User::findOrFail($recipientId);
            
            // Check if thread already exists
            $thread = MessageThread::where(function($query) use ($user, $recipient) {
                $query->where('user1_id', $user->id)
                      ->where('user2_id', $recipient->id);
            })->orWhere(function($query) use ($user, $recipient) {
                $query->where('user1_id', $recipient->id)
                      ->where('user2_id', $user->id);
            })->first();

            if ($thread) {
                return redirect()->route('messages.show', $thread->id);
            }

            return view('messages.create', [
                'recipient' => $recipient,
            ]);
        }

        return view('messages.create');
    }

    /**
     * Store a new message.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'body' => ['required', 'string', 'max:5000'],
            'thread_id' => ['nullable', 'exists:message_threads,id'],
            'portfolio_image_id' => ['nullable', 'exists:portfolio_images,id'],
        ]);

        $recipient = User::findOrFail($validated['recipient_id']);

        // Prevent messaging yourself
        if ($recipient->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You cannot message yourself.'], 422);
            }

            return back()->withErrors(['body' => 'You cannot message yourself.']);
        }

        // Get or create thread
        if ($validated['thread_id'] ?? null) {
            $thread = MessageThread::findOrFail($validated['thread_id']);
            // Verify user is part of thread
            if ($thread->user1_id !== $user->id && $thread->user2_id !== $user->id) {
                abort(403);
            }
        } else {
            $thread = $this->findOrCreateThread($user, $recipient);
        }

        // Verify image belongs to sender if provided
        if ($validated['portfolio_image_id'] ?? null) {
            $image = \App\Models\PortfolioImage::findOrFail($validated['portfolio_image_id']);
            if ($image->model_id !== $user->id && $image->photographer_id !== $user->id) {
                return back()->withErrors(['portfolio_image_id' => 'You can only attach your own images.']);
            }
        }

        // Create message
        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'portfolio_image_id' => $validated['portfolio_image_id'] ?? null,
        ])->load('sender');

        // Update thread last message time
        $thread->update(['last_message_at' => now()]);

        SiteNotification::notifyMessage($recipient, $user, $thread);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Message sent.',
                'thread' => $this->threadPayload($thread->fresh(['user1', 'user2']), $user),
                'sent_message' => $this->messagePayload($message, $user),
            ]);
        }

        return redirect()->route('messages.show', $thread->id)
            ->with('status', 'Message sent.');
    }

    /**
     * Delete a message thread.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = Auth::user();
        $thread = MessageThread::findOrFail($id);

        // Verify user is part of thread
        if ($thread->user1_id !== $user->id && $thread->user2_id !== $user->id) {
            abort(403);
        }

        $thread->delete();

        return redirect()->route('messages.index')
            ->with('status', 'Conversation deleted.');
    }

    public function unsend(Message $message): JsonResponse
    {
        $user = Auth::user();
        $thread = $message->thread;

        if ((int) $thread->user1_id !== (int) $user->id && (int) $thread->user2_id !== (int) $user->id) {
            abort(403);
        }

        if (!$message->canBeUnsentBy($user)) {
            return response()->json([
                'message' => 'Messages can only be unsent within 5 minutes of sending.',
            ], 422);
        }

        $message->forceFill([
            'body' => '',
            'portfolio_image_id' => null,
            'unsent_at' => now(),
            'unsent_by_user_id' => $user->id,
        ])->save();

        return response()->json([
            'message' => 'Message unsent.',
            'thread' => $this->threadPayload($thread->fresh(['user1', 'user2']), $user),
        ]);
    }

    private function sendNewMessageNotification(User $recipient, User $sender, string $body): void
    {
        $recipientEmail = $recipient->modelProfile?->public_email
            ?? $recipient->photographerProfile?->public_email
            ?? $recipient->email;

        if (!$recipientEmail) {
            return;
        }

        try {
            MailConfigService::configure();

            $subject = 'New message on ASDF Models';
            $preview = trim(mb_substr($body, 0, 300));
            $messagesUrl = route('messages.index');

            Mail::raw(
                "You have a new message on ASDF Models from {$sender->name}.\n\n"
                . ($preview !== '' ? "Message preview:\n{$preview}\n\n" : '')
                . "View and reply here: {$messagesUrl}",
                function ($message) use ($recipientEmail, $subject) {
                    $message->to($recipientEmail)->subject($subject);
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Unable to send new message notification email: ' . $e->getMessage());
        }
    }

    private function findOrCreateThread(User $user, User $recipient): MessageThread
    {
        $thread = MessageThread::where(function($query) use ($user, $recipient) {
            $query->where('user1_id', $user->id)
                ->where('user2_id', $recipient->id);
        })->orWhere(function($query) use ($user, $recipient) {
            $query->where('user1_id', $recipient->id)
                ->where('user2_id', $user->id);
        })->first();

        if ($thread) {
            return $thread;
        }

        return MessageThread::create([
            'user1_id' => min($user->id, $recipient->id),
            'user2_id' => max($user->id, $recipient->id),
        ]);
    }

    private function threadsFor(User $user)
    {
        return MessageThread::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with(['user1.modelProfile', 'user1.photographerProfile', 'user2.modelProfile', 'user2.photographerProfile', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC');
    }

    private function threadSummaryPayload(MessageThread $thread, User $viewer): array
    {
        $otherUser = $thread->otherUser($viewer->id);
        $latest = $thread->messages->first();

        return [
            'id' => $thread->id,
            'url' => route('messages.show', $thread->id),
            'thread_url' => route('messages.thread', $thread->id),
            'recipient' => $this->userPayload($otherUser),
            'unread_count' => $thread->unreadCount($viewer->id),
            'last_message_at' => $thread->last_message_at?->diffForHumans(),
            'preview' => $latest
                ? ($latest->unsent_at ? 'Message unsent' : str($latest->body)->limit(110)->toString())
                : 'No messages yet.',
        ];
    }

    private function threadPayload(MessageThread $thread, User $viewer): array
    {
        Message::where('thread_id', $thread->id)
            ->where('sender_id', '!=', $viewer->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        SiteNotification::where('user_id', $viewer->id)
            ->where('type', 'message')
            ->where('data->thread_id', $thread->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $otherUser = $thread->otherUser($viewer->id);
        $messages = $thread->messages()->with('sender')->limit(80)->get();

        return [
            'id' => $thread->id,
            'url' => route('messages.show', $thread->id),
            'recipient' => $this->userPayload($otherUser),
            'messages' => $messages->map(fn (Message $message) => $this->messagePayload($message, $viewer))->values(),
        ];
    }

    private function messagePayload(Message $message, User $viewer): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'is_mine' => (int) $message->sender_id === (int) $viewer->id,
            'is_unsent' => $message->unsent_at !== null,
            'can_unsend' => $message->canBeUnsentBy($viewer),
            'sender' => $this->userPayload($message->sender),
            'created_at' => $message->created_at?->format('M j, H:i'),
            'created_at_iso' => $message->created_at?->toIso8601String(),
        ];
    }

    private function userPayload(?User $user): array
    {
        if (!$user) {
            return [
                'id' => null,
                'name' => 'Member',
                'avatar' => null,
                'initials' => 'M',
            ];
        }

        $profile = $user->is_photographer ? $user->photographerProfile : $user->modelProfile;
        $name = $profile?->display_name ?: $user->display_name ?: $user->name;
        $parts = preg_split('/\s+/', trim($name));
        $initials = count($parts) > 1
            ? strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1))
            : strtoupper(substr($name, 0, 1));

        return [
            'id' => $user->id,
            'name' => $name,
            'avatar' => $profile?->profile_photo_path ? asset($profile->profile_photo_path) : null,
            'initials' => $initials,
        ];
    }
}
