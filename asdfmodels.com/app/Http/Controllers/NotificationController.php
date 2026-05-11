<?php

namespace App\Http\Controllers;

use App\Models\PortfolioCredit;
use App\Models\SiteNotification;
use App\Models\PortfolioAlbum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function summary(): JsonResponse
    {
        $user = Auth::user();
        $role = $user->is_photographer ? 'photographer' : 'model';

        $pendingCredits = PortfolioCredit::awaitingResponse($user, $role)
            ->with(['creditable', 'owner'])
            ->latest()
            ->get();

        $creditGroups = $pendingCredits
            ->groupBy(function (PortfolioCredit $credit) {
                $creditable = $credit->creditable;
                $gallery = $creditable instanceof PortfolioAlbum ? $creditable : $creditable?->album;

                return $gallery ? 'gallery:' . $gallery->id : 'single:' . $credit->id;
            })
            ->map(function ($credits) {
                /** @var \Illuminate\Support\Collection<int, PortfolioCredit> $credits */
                $firstCredit = $credits->first();
                $creditable = $firstCredit->creditable;
                $gallery = $creditable instanceof PortfolioAlbum ? $creditable : $creditable?->album;
                $ownerName = $firstCredit->owner?->display_name ?: $firstCredit->owner?->name ?: 'A member';
                $itemLabel = $credits->count() === 1 ? 'credit request' : $credits->count() . ' credit requests';

                return [
                    'title' => $gallery ? $gallery->name : 'Individual photo',
                    'body' => "{$ownerName} sent {$itemLabel}.",
                    'count' => $credits->count(),
                    'url' => route('notifications.index'),
                ];
            })
            ->values()
            ->take(4);

        $notifications = SiteNotification::where('user_id', $user->id)
            ->whereNotIn('type', ['credit_pending', 'message'])
            ->with('actor')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (SiteNotification $notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'body' => $notification->body,
                'url' => $notification->action_url ?: route('notifications.index'),
                'is_unread' => $notification->read_at === null,
                'created_at' => $notification->created_at?->diffForHumans(),
                'actor' => $notification->actor?->display_name ?: $notification->actor?->name,
                'data' => $notification->data ?? [],
            ]);

        $unreadOtherCount = SiteNotification::where('user_id', $user->id)
            ->whereNotIn('type', ['credit_pending', 'message'])
            ->unread()
            ->count();

        return response()->json([
            'unread_count' => $pendingCredits->count() + $unreadOtherCount,
            'credit_count' => $pendingCredits->count(),
            'credits' => $creditGroups,
            'notifications' => $notifications,
        ]);
    }

    public function index(): View
    {
        $user = Auth::user();
        $pendingCredits = PortfolioCredit::awaitingResponse($user, $user->is_photographer ? 'photographer' : 'model')
            ->with(['creditable', 'owner'])
            ->latest()
            ->get();

        $creditGroups = $pendingCredits->groupBy(function (PortfolioCredit $credit) {
            $creditable = $credit->creditable;
            $gallery = $creditable instanceof \App\Models\PortfolioAlbum ? $creditable : $creditable?->album;

            return $gallery ? 'gallery:' . $gallery->id : 'single:' . $credit->id;
        });

        $notifications = SiteNotification::where('user_id', $user->id)
            ->whereNotIn('type', ['credit_pending', 'message'])
            ->with('actor')
            ->latest()
            ->paginate(20);

        $unreadOtherCount = SiteNotification::where('user_id', $user->id)
            ->whereNotIn('type', ['credit_pending', 'message'])
            ->unread()
            ->count();

        return view('notifications.index', [
            'creditGroups' => $creditGroups,
            'notifications' => $notifications,
            'unreadCount' => $pendingCredits->count() + $unreadOtherCount,
            'unreadOtherCount' => $unreadOtherCount,
        ]);
    }

    public function updateCreditStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credit_ids' => ['required', 'array', 'min:1'],
            'credit_ids.*' => ['integer', 'exists:portfolio_credits,id'],
            'status' => ['required', Rule::in(['accepted_visible', 'accepted_hidden', 'rejected'])],
        ]);

        $credits = PortfolioCredit::where('credited_user_id', Auth::id())
            ->whereIn('id', $validated['credit_ids'])
            ->get();

        foreach ($credits as $credit) {
            match ($validated['status']) {
                PortfolioCredit::STATUS_ACCEPTED_VISIBLE => $credit->acceptVisible(),
                PortfolioCredit::STATUS_ACCEPTED_HIDDEN => $credit->acceptHidden(),
                PortfolioCredit::STATUS_REJECTED => $credit->reject(),
            };

            SiteNotification::where('user_id', Auth::id())
                ->where('type', 'credit_pending')
                ->where('data->credit_id', $credit->id)
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Credit preferences updated.',
            'updated' => $credits->count(),
        ]);
    }

    public function markRead(SiteNotification $notification): RedirectResponse
    {
        if ((int) $notification->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $notification->markRead();

        return redirect($notification->action_url ?: route('notifications.index'));
    }

    public function markAllRead(): RedirectResponse
    {
        SiteNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'Notifications marked as read.');
    }
}
