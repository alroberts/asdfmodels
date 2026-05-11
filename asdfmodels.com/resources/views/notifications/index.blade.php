<x-app-layout>
    @push('styles')
        <style>
            .notifications-page {
                margin: 0 auto;
                max-width: 1120px;
                padding: 46px 24px 72px;
            }

            .notifications-header {
                align-items: flex-end;
                display: flex;
                gap: 20px;
                justify-content: space-between;
                margin-bottom: 28px;
            }

            .notifications-kicker {
                color: #6b7280;
                font-size: 12px;
                font-weight: 850;
                letter-spacing: .22em;
                text-transform: uppercase;
            }

            .notifications-title {
                color: #050505;
                font-size: clamp(34px, 5vw, 58px);
                font-weight: 850;
                line-height: .95;
                margin: 8px 0 0;
            }

            .notifications-mark-read {
                background: #fff;
                border: 1px solid #d1d5db;
                border-radius: 999px;
                color: #111827;
                font-size: 13px;
                font-weight: 800;
                padding: 10px 14px;
            }

            .notification-section {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 22px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
                margin-bottom: 22px;
                overflow: hidden;
            }

            .notification-section-head {
                align-items: center;
                border-bottom: 1px solid #eef2f7;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                padding: 20px 22px;
            }

            .notification-section-head h2 {
                color: #111827;
                font-size: 20px;
                font-weight: 850;
                margin: 0;
            }

            .notification-section-head p {
                color: #64748b;
                font-size: 14px;
                margin: 5px 0 0;
            }

            .credit-group {
                border-bottom: 1px solid #eef2f7;
                padding: 20px 22px;
            }

            .credit-group:last-child {
                border-bottom: 0;
            }

            .credit-group-head {
                align-items: flex-start;
                display: flex;
                gap: 18px;
                justify-content: space-between;
                margin-bottom: 14px;
            }

            .credit-group h3 {
                color: #111827;
                font-size: 18px;
                font-weight: 850;
                margin: 0;
            }

            .credit-group-meta {
                color: #64748b;
                font-size: 13px;
                margin-top: 5px;
            }

            .credit-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .credit-actions button {
                border-radius: 999px;
                font-size: 12px;
                font-weight: 850;
                padding: 9px 12px;
            }

            .credit-actions .primary {
                background: #050505;
                border: 1px solid #050505;
                color: #fff;
            }

            .credit-actions .secondary {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #374151;
            }

            .credit-items {
                display: grid;
                gap: 10px;
                grid-template-columns: repeat(auto-fill, minmax(138px, 1fr));
            }

            .credit-item {
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                cursor: pointer;
                overflow: hidden;
                position: relative;
            }

            .credit-item input {
                left: 10px;
                position: absolute;
                top: 10px;
                z-index: 2;
            }

            .credit-thumb {
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
            }

            .credit-thumb img {
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .credit-item-body {
                padding: 10px;
            }

            .credit-item-body strong,
            .credit-item-body span {
                display: block;
            }

            .credit-item-body strong {
                color: #111827;
                font-size: 13px;
            }

            .credit-item-body span {
                color: #64748b;
                font-size: 11px;
                font-weight: 800;
                margin-top: 4px;
                text-transform: uppercase;
            }

            .notification-list {
                display: grid;
            }

            .notification-row {
                align-items: center;
                border-bottom: 1px solid #eef2f7;
                color: inherit;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                padding: 16px 22px;
                text-decoration: none;
            }

            .notification-row:last-child {
                border-bottom: 0;
            }

            .notification-row.is-unread {
                background: #f8fafc;
            }

            .notification-row strong {
                color: #111827;
                display: block;
                font-size: 15px;
            }

            .notification-row span {
                color: #64748b;
                display: block;
                font-size: 13px;
                margin-top: 3px;
            }

            .notifications-empty {
                color: #64748b;
                padding: 30px 22px;
                text-align: center;
            }

            @media (max-width: 720px) {
                .notifications-header,
                .credit-group-head {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .credit-actions {
                    justify-content: flex-start;
                }
            }
        </style>
    @endpush

    <main class="notifications-page">
        <header class="notifications-header">
            <div>
                <p class="notifications-kicker">Activity Centre</p>
                <h1 class="notifications-title">Notifications</h1>
            </div>
            @if(($unreadOtherCount ?? 0) > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="notifications-mark-read">Mark all read</button>
                </form>
            @endif
        </header>

        <section class="notification-section">
            <div class="notification-section-head">
                <div>
                    <h2>Credits & Tags</h2>
                    <p>Review gallery and image credits before they appear on your profile.</p>
                </div>
            </div>

            @if($creditGroups->isNotEmpty())
                @foreach($creditGroups as $groupKey => $credits)
                    @php
                        $firstCredit = $credits->first();
                        $creditable = $firstCredit->creditable;
                        $gallery = $creditable instanceof \App\Models\PortfolioAlbum ? $creditable : $creditable?->album;
                        $ownerName = $firstCredit->owner?->display_name ?? $firstCredit->owner?->name ?? 'A member';
                        $galleryName = $gallery?->name ?? 'Individual photos';
                    @endphp
                    <article class="credit-group" data-credit-group>
                        <div class="credit-group-head">
                            <div>
                                <h3>{{ $galleryName }}</h3>
                                <p class="credit-group-meta">{{ $ownerName }} · {{ $credits->count() }} pending {{ $credits->count() === 1 ? 'credit' : 'credits' }}</p>
                            </div>
                            <div class="credit-actions">
                                <button type="button" class="primary" data-credit-bulk="accepted_visible">Accept All</button>
                                <button type="button" class="secondary" data-credit-bulk="accepted_hidden">Accept Hidden</button>
                                <button type="button" class="secondary" data-credit-bulk="rejected">Decline</button>
                            </div>
                        </div>

                        <div class="credit-items">
                            @foreach($credits as $credit)
                                @php
                                    $item = $credit->creditable;
                                    $isGallery = $item instanceof \App\Models\PortfolioAlbum;
                                    $thumb = null;
                                    if ($isGallery) {
                                        $thumb = $item->cover_image_path ?? $item->coverImage?->thumbnail_path;
                                    } else {
                                        $thumb = $item?->thumbnail_path ?? $item?->full_path;
                                    }
                                @endphp
                                <label class="credit-item" data-credit-item="{{ $credit->id }}">
                                    <input type="checkbox" value="{{ $credit->id }}" checked data-credit-checkbox>
                                    <div class="credit-thumb">
                                        @if($thumb)
                                            <img src="{{ asset($thumb) }}" alt="">
                                        @else
                                            <div style="align-items:center;display:flex;height:100%;justify-content:center;color:#94a3b8;"><i class="fas fa-images"></i></div>
                                        @endif
                                    </div>
                                    <div class="credit-item-body">
                                        <strong>{{ $isGallery ? 'Full gallery' : 'Image credit' }}</strong>
                                        <span>{{ $credit->source === 'tag_request' ? 'Requested tag' : 'Tagged by owner' }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            @else
                <div class="notifications-empty">No credit requests waiting for review.</div>
            @endif
        </section>

        <section class="notification-section">
            <div class="notification-section-head">
                <div>
                    <h2>Connection Requests</h2>
                    <p>Accept requests from members you want to keep in your network.</p>
                </div>
            </div>

            @if(($connectionRequests ?? collect())->isNotEmpty())
                <div class="notification-list">
                    @foreach($connectionRequests as $connection)
                        @php($requester = $connection->requester)
                        <div class="notification-row is-unread">
                            <div>
                                <strong>{{ $requester?->display_name ?: $requester?->name ?: 'A member' }}</strong>
                                <span>{{ $connection->message ?: 'Wants to connect with you.' }}</span>
                            </div>
                            <div class="credit-actions">
                                <form method="POST" action="{{ route('connections.accept', $connection) }}">
                                    @csrf
                                    <button type="submit" class="primary">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('connections.decline', $connection) }}">
                                    @csrf
                                    <button type="submit" class="secondary">Decline</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="notifications-empty">No connection requests waiting for review.</div>
            @endif
        </section>

        <section class="notification-section">
            <div class="notification-section-head">
                <div>
                    <h2>Messages & Account</h2>
                    <p>Messages, site updates, and future admin announcements will appear here.</p>
                </div>
            </div>

            @if($notifications->count() > 0)
                <div class="notification-list">
                    @foreach($notifications as $notification)
                        <a href="{{ route('notifications.open', $notification->id) }}" class="notification-row {{ $notification->read_at ? '' : 'is-unread' }}">
                            <div>
                                <strong>{{ $notification->title }}</strong>
                                <span>{{ $notification->body }}</span>
                            </div>
                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                        </a>
                    @endforeach
                </div>
                <div style="padding: 16px 22px;">{{ $notifications->links() }}</div>
            @else
                <div class="notifications-empty">No other notifications yet.</div>
            @endif
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-credit-bulk]').forEach((button) => {
            button.addEventListener('click', async () => {
                const group = button.closest('[data-credit-group]');
                const creditIds = Array.from(group.querySelectorAll('[data-credit-checkbox]:checked')).map((box) => box.value);

                if (creditIds.length === 0) {
                    return;
                }

                button.disabled = true;
                const response = await fetch('{{ route('notifications.credits.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        credit_ids: creditIds,
                        status: button.dataset.creditBulk
                    })
                });

                button.disabled = false;

                if (!response.ok) {
                    return;
                }

                creditIds.forEach((creditId) => {
                    group.querySelector(`[data-credit-item="${creditId}"]`)?.remove();
                });

                if (!group.querySelector('[data-credit-item]')) {
                    group.remove();
                }
            });
        });
    </script>
</x-app-layout>
