<?php

namespace App\Http\Controllers;

use App\Models\FeedPost;
use App\Models\FeedPostMention;
use App\Models\SiteNotification;
use App\Services\FeedPostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        $posts = FeedPost::visibleTo($user)
            ->with(['user.modelProfile', 'user.photographerProfile', 'images', 'mentions.mentionedUser', 'related'])
            ->latest()
            ->paginate(12);

        $pendingMentions = FeedPostMention::where('mentioned_user_id', $user->id)
            ->where('status', FeedPostMention::STATUS_PENDING)
            ->with(['post.user.modelProfile', 'post.user.photographerProfile'])
            ->latest()
            ->get();

        return view('dashboard', [
            'posts' => $posts,
            'pendingMentions' => $pendingMentions,
        ]);
    }

    public function store(Request $request, FeedPostService $feedPostService): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        if (blank($validated['body'] ?? null) && blank($validated['link_url'] ?? null) && !$request->hasFile('images')) {
            return back()->withErrors(['body' => 'Write something, add images, or share a link.'])->withInput();
        }

        $feedPostService->createPost(Auth::user(), $validated, $request->file('images', []));

        return back()->with('status', 'Post shared.');
    }

    public function updateMention(Request $request, FeedPostMention $mention): RedirectResponse
    {
        if ((int) $mention->mentioned_user_id !== (int) Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                FeedPostMention::STATUS_ACCEPTED_VISIBLE,
                FeedPostMention::STATUS_ACCEPTED_HIDDEN,
                FeedPostMention::STATUS_REJECTED,
            ])],
        ]);

        $mention->respond($validated['status']);

        SiteNotification::where('user_id', Auth::id())
            ->where('type', 'feed_mention')
            ->where('data->feed_post_mention_id', $mention->id)
            ->update(['read_at' => now()]);

        return back()->with('status', 'Feed mention preference saved.');
    }
}
