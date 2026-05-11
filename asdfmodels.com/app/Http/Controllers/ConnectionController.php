<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\SiteNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConnectionController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $connections = Connection::query()
            ->with([
                'requester.modelProfile',
                'requester.photographerProfile',
                'recipient.modelProfile',
                'recipient.photographerProfile',
            ])
            ->where(function ($query) use ($user) {
                $query->where('requester_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->latest('updated_at')
            ->get();

        return view('connections.index', [
            'acceptedConnections' => $connections
                ->where('status', Connection::STATUS_ACCEPTED)
                ->map(fn (Connection $connection) => $this->connectionCard($connection, $user))
                ->values(),
            'receivedRequests' => $connections
                ->where('status', Connection::STATUS_PENDING)
                ->where('recipient_id', $user->id)
                ->map(fn (Connection $connection) => $this->connectionCard($connection, $user))
                ->values(),
            'sentRequests' => $connections
                ->where('status', Connection::STATUS_PENDING)
                ->where('requester_id', $user->id)
                ->map(fn (Connection $connection) => $this->connectionCard($connection, $user))
                ->values(),
            'blockedConnections' => $connections
                ->where('status', Connection::STATUS_BLOCKED)
                ->filter(fn (Connection $connection) => (int) $connection->blocked_by_user_id === (int) $user->id)
                ->map(fn (Connection $connection) => $this->connectionCard($connection, $user))
                ->values(),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $viewer = Auth::user();

        if ($viewer->id === $user->id) {
            return back()->with('error', 'You cannot connect with yourself.');
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:125'],
        ]);

        $existing = Connection::between($viewer, $user)->first();

        if ($existing) {
            if ($existing->status === Connection::STATUS_DECLINED) {
                $existing->forceFill([
                    'requester_id' => $viewer->id,
                    'recipient_id' => $user->id,
                    'status' => Connection::STATUS_PENDING,
                    'message' => $validated['message'] ?? null,
                    'responded_at' => null,
                ])->save();

                SiteNotification::notifyConnectionRequest($existing->fresh(['requester', 'recipient']));

                return back()->with('status', 'Connection request sent.');
            }

            return back()->with('status', $existing->status === Connection::STATUS_ACCEPTED
                ? 'You are already connected.'
                : 'Connection request is already pending.');
        }

        $connection = Connection::create([
            'requester_id' => $viewer->id,
            'recipient_id' => $user->id,
            'status' => Connection::STATUS_PENDING,
            'message' => $validated['message'] ?? null,
        ]);

        SiteNotification::notifyConnectionRequest($connection->load(['requester', 'recipient']));

        return back()->with('status', 'Connection request sent.');
    }

    public function accept(Connection $connection): RedirectResponse
    {
        $this->authorizeConnectionRecipient($connection);
        $connection->accept();
        $this->markConnectionNotificationRead($connection);

        return back()->with('status', 'Connection accepted.');
    }

    public function decline(Connection $connection): RedirectResponse
    {
        $this->authorizeConnectionRecipient($connection);
        $connection->decline();
        $this->markConnectionNotificationRead($connection);

        return back()->with('status', 'Connection declined.');
    }

    public function destroy(Connection $connection): RedirectResponse
    {
        if (!in_array(Auth::id(), [(int) $connection->requester_id, (int) $connection->recipient_id], true)) {
            abort(403);
        }

        $connection->delete();

        return back()->with('status', 'Connection removed.');
    }

    public function block(Connection $connection): RedirectResponse
    {
        if (!in_array(Auth::id(), [(int) $connection->requester_id, (int) $connection->recipient_id], true)) {
            abort(403);
        }

        $connection->block(Auth::id());

        return back()->with('status', 'Connection blocked.');
    }

    private function authorizeConnectionRecipient(Connection $connection): void
    {
        if ((int) $connection->recipient_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    private function markConnectionNotificationRead(Connection $connection): void
    {
        SiteNotification::where('user_id', Auth::id())
            ->where('type', 'connection_request')
            ->where('data->connection_id', $connection->id)
            ->update(['read_at' => now()]);
    }

    private function connectionCard(Connection $connection, User $viewer): array
    {
        $connectedUser = $connection->otherUser($viewer);
        $profile = $connectedUser?->is_photographer
            ? $connectedUser->photographerProfile
            : $connectedUser?->modelProfile;
        $displayName = $profile?->display_name ?: $connectedUser?->display_name ?: $connectedUser?->name;
        $role = $connectedUser?->is_photographer ? 'Photographer' : 'Model';
        $profileRoute = $connectedUser?->is_photographer
            ? route('photographers.show', $connectedUser->profileRouteIdentifier())
            : route('models.show', $connectedUser->profileRouteIdentifier());

        return [
            'connection' => $connection,
            'user' => $connectedUser,
            'display_name' => $displayName,
            'username' => $connectedUser?->username,
            'role' => $role,
            'avatar' => $profile?->profile_photo_path,
            'profile_route' => $profileRoute,
            'location' => collect([$profile?->location_city, $profile?->location_country])->filter()->implode(', '),
            'message' => $connection->message,
            'is_received' => (int) $connection->recipient_id === (int) $viewer->id,
        ];
    }
}
