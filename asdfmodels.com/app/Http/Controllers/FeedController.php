<?php

namespace App\Http\Controllers;

use App\Models\FeedPost;
use App\Models\FeedPostMention;
use App\Models\Connection;
use App\Models\SiteNotification;
use App\Models\User;
use App\Services\FeedPostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
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

    public function show(FeedPost $post): View
    {
        $user = Auth::user();

        $post->load(['user.modelProfile', 'user.photographerProfile', 'images', 'mentions.mentionedUser.modelProfile', 'mentions.mentionedUser.photographerProfile', 'related']);

        if (!$this->canViewPost($post, $user)) {
            abort(403);
        }

        $pendingMention = $post->mentions
            ->first(fn (FeedPostMention $mention) => (int) $mention->mentioned_user_id === (int) $user->id && $mention->status === FeedPostMention::STATUS_PENDING);

        SiteNotification::where('user_id', $user->id)
            ->where('type', 'feed_mention')
            ->where('data->feed_post_id', $post->id)
            ->update(['read_at' => now()]);

        return view('feed.show', [
            'post' => $post,
            'pendingMention' => $pendingMention,
        ]);
    }

    public function store(Request $request, FeedPostService $feedPostService): RedirectResponse|JsonResponse
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

        $post = $feedPostService->createPost(Auth::user(), $validated, $request->file('images', []));

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post shared.',
                'post_html' => view('feed.partials.post-card', ['post' => $post])->render(),
            ]);
        }

        return back()->with('status', 'Post shared.');
    }

    public function previewLink(Request $request, FeedPostService $feedPostService): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:500'],
        ]);

        return response()->json([
            'success' => true,
            'preview' => $feedPostService->previewLink(Auth::user(), $validated['url']),
        ]);
    }

    public function mentionSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $search = ltrim(trim((string) ($validated['q'] ?? '')), '@');

        if (mb_strlen($search) < 2) {
            return response()->json(['users' => []]);
        }

        $viewer = Auth::user();

        $users = User::query()
            ->where('is_admin', false)
            ->where('id', '!=', $viewer->id)
            ->whereNotNull('username')
            ->where(function ($query) {
                $query
                    ->whereHas('modelProfile', fn ($profileQuery) => $profileQuery->where('is_public', true))
                    ->orWhereHas('photographerProfile', fn ($profileQuery) => $profileQuery->where('is_public', true));
            })
            ->where(function ($query) use ($search) {
                $query
                    ->where('username', 'like', "{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "{$search}%")
                    ->orWhere('last_name', 'like', "{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->with(['modelProfile', 'photographerProfile'])
            ->orderByRaw('CASE WHEN username LIKE ? THEN 0 ELSE 1 END', [$search . '%'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(8)
            ->get()
            ->map(function (User $user) {
                $profile = $user->is_photographer ? $user->photographerProfile : $user->modelProfile;
                $route = $user->is_photographer
                    ? route('photographers.show', $user->profileRouteIdentifier())
                    : route('models.show', $user->profileRouteIdentifier());

                return [
                    'id' => $user->id,
                    'label' => $profile?->display_name ?: $user->display_name ?: $user->name,
                    'username' => $user->username,
                    'role' => $user->is_photographer ? 'Photographer' : 'Model',
                    'avatar' => $profile?->profile_photo_path ? asset($profile->profile_photo_path) : null,
                    'url' => $route,
                ];
            })
            ->values();

        return response()->json(['users' => $users]);
    }

    public function updateMention(Request $request, FeedPostMention $mention): RedirectResponse|JsonResponse
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

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Feed mention preference saved.',
            ]);
        }

        return back()->with('status', 'Feed mention preference saved.');
    }

    private function canViewPost(FeedPost $post, User $user): bool
    {
        if ((int) $post->user_id === (int) $user->id) {
            return true;
        }

        if ($post->mentions->contains(fn (FeedPostMention $mention) => (int) $mention->mentioned_user_id === (int) $user->id)) {
            return true;
        }

        return Connection::acceptedFor($user)
            ->where(function ($query) use ($post) {
                $query
                    ->where('requester_id', $post->user_id)
                    ->orWhere('recipient_id', $post->user_id);
            })
            ->exists();
    }
}
