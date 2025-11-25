<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Display inbox with all message threads.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        $threads = MessageThread::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with(['user1', 'user2', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function($thread) use ($user) {
                $thread->other_user = $thread->otherUser($user->id);
                $thread->unread_count = $thread->unreadCount($user->id);
                return $thread;
            });

        return view('messages.index', [
            'threads' => $threads,
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

        // Mark messages as read
        Message::where('thread_id', $thread->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = $thread->messages()->with(['sender', 'portfolioImage'])->get();
        $otherUser = $thread->otherUser($user->id);

        return view('messages.show', [
            'thread' => $thread,
            'messages' => $messages,
            'otherUser' => $otherUser,
        ]);
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
    public function store(Request $request): RedirectResponse
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
            // Check if thread exists
            $thread = MessageThread::where(function($query) use ($user, $recipient) {
                $query->where('user1_id', $user->id)
                      ->where('user2_id', $recipient->id);
            })->orWhere(function($query) use ($user, $recipient) {
                $query->where('user1_id', $recipient->id)
                      ->where('user2_id', $user->id);
            })->first();

            if (!$thread) {
                // Create new thread (always put lower ID as user1)
                $user1Id = min($user->id, $recipient->id);
                $user2Id = max($user->id, $recipient->id);
                
                $thread = MessageThread::create([
                    'user1_id' => $user1Id,
                    'user2_id' => $user2Id,
                ]);
            }
        }

        // Verify image belongs to sender if provided
        if ($validated['portfolio_image_id'] ?? null) {
            $image = \App\Models\PortfolioImage::findOrFail($validated['portfolio_image_id']);
            if ($image->model_id !== $user->id && $image->photographer_id !== $user->id) {
                return back()->withErrors(['portfolio_image_id' => 'You can only attach your own images.']);
            }
        }

        // Create message
        Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'portfolio_image_id' => $validated['portfolio_image_id'] ?? null,
        ]);

        // Update thread last message time
        $thread->update(['last_message_at' => now()]);

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
}

