<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\SiteNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
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
}
