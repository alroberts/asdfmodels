<x-app-layout>
    @push('styles')
        <style>
            .connections-page {
                background: #fff;
                min-height: calc(100vh - 66px);
                padding: 46px 24px 64px;
            }

            .connections-shell {
                margin: 0 auto;
                max-width: 1120px;
            }

            .connections-hero {
                align-items: end;
                display: flex;
                gap: 24px;
                justify-content: space-between;
                margin-bottom: 26px;
            }

            .connections-kicker {
                color: #6b7280;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .24em;
                margin: 0 0 8px;
                text-transform: uppercase;
            }

            .connections-title {
                color: #050505;
                font-size: clamp(30px, 4vw, 42px);
                font-weight: 800;
                letter-spacing: -.03em;
                line-height: 1.05;
                margin: 0;
            }

            .connections-copy {
                color: #4b5563;
                line-height: 1.7;
                margin: 14px 0 0;
                max-width: 680px;
            }

            .connections-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 18px;
            }

            .connections-tab {
                background: #fff;
                border: 1px solid #d1d5db;
                border-radius: 999px;
                color: #111827;
                display: inline-flex;
                font-size: 13px;
                font-weight: 700;
                gap: 8px;
                padding: 10px 14px;
                text-decoration: none;
            }

            .connections-section {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 18px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1), 0 1px 2px rgba(15, 23, 42, 0.06);
                margin-top: 18px;
                overflow: hidden;
            }

            .connections-section-header {
                align-items: center;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                padding: 22px 24px;
            }

            .connections-section-title {
                color: #050505;
                font-size: 20px;
                font-weight: 700;
                margin: 0;
            }

            .connections-count {
                background: #050505;
                border-radius: 999px;
                color: #fff;
                font-size: 12px;
                font-weight: 700;
                padding: 7px 10px;
            }

            .connections-grid {
                display: grid;
                gap: 14px;
                grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
                padding: 18px;
            }

            .connection-card {
                border: 1px solid #e5e7eb;
                border-radius: 20px;
                display: grid;
                gap: 14px;
                padding: 16px;
            }

            .connection-card-top {
                align-items: center;
                display: flex;
                gap: 13px;
                min-width: 0;
            }

            .connection-avatar {
                align-items: center;
                background: #111827;
                border-radius: 999px;
                color: #fff;
                display: flex;
                flex: 0 0 auto;
                font-weight: 800;
                height: 54px;
                justify-content: center;
                overflow: hidden;
                width: 54px;
            }

            .connection-avatar img {
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .connection-name {
                color: #050505;
                font-size: 16px;
                font-weight: 700;
                margin: 0;
            }

            .connection-meta {
                color: #6b7280;
                font-size: 13px;
                font-weight: 500;
                margin-top: 3px;
            }

            .connection-note {
                background: #f9fafb;
                border-radius: 14px;
                color: #4b5563;
                font-size: 13px;
                line-height: 1.6;
                padding: 12px;
            }

            .connection-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .connection-action {
                align-items: center;
                border-radius: 999px;
                display: inline-flex;
                font-size: 12px;
                font-weight: 700;
                gap: 7px;
                padding: 9px 11px;
                text-decoration: none;
            }

            .connection-action-primary {
                background: #050505;
                border: 1px solid #050505;
                color: #fff;
            }

            .connection-action-secondary {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #374151;
            }

            .connection-action-danger {
                background: #fff;
                border: 1px solid #fecaca;
                color: #b91c1c;
            }

            .connection-empty {
                color: #6b7280;
                padding: 28px 24px;
            }

            @media (max-width: 720px) {
                .connections-page {
                    padding: 30px 16px 48px;
                }

                .connections-hero,
                .connections-section-header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .connections-grid {
                    grid-template-columns: 1fr;
                    padding: 12px;
                }
            }
        </style>
    @endpush

    @php
        $sections = [
            ['id' => 'connected', 'title' => 'Connected Members', 'items' => $acceptedConnections, 'empty' => 'Accepted connections will appear here.'],
            ['id' => 'received', 'title' => 'Requests To Review', 'items' => $receivedRequests, 'empty' => 'No connection requests need your attention right now.'],
            ['id' => 'sent', 'title' => 'Sent Requests', 'items' => $sentRequests, 'empty' => 'Requests you send will appear here until accepted.'],
            ['id' => 'blocked', 'title' => 'Blocked Members', 'items' => $blockedConnections, 'empty' => 'Blocked members will appear here.'],
        ];
    @endphp

    <main class="connections-page">
        <div class="connections-shell">
            <header class="connections-hero">
                <div>
                    <p class="connections-kicker">Member Network</p>
                    <h1 class="connections-title">Connections</h1>
                    <p class="connections-copy">Manage the people you work with, review requests, message collaborators, and jump into tagging from one place.</p>
                </div>
            </header>

            <nav class="connections-tabs" aria-label="Connection sections">
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}" class="connections-tab">{{ $section['title'] }} <span>{{ $section['items']->count() }}</span></a>
                @endforeach
            </nav>

            @foreach($sections as $section)
                <section class="connections-section" id="{{ $section['id'] }}">
                    <div class="connections-section-header">
                        <h2 class="connections-section-title">{{ $section['title'] }}</h2>
                        <span class="connections-count">{{ $section['items']->count() }}</span>
                    </div>

                    @if($section['items']->isEmpty())
                        <p class="connection-empty">{{ $section['empty'] }}</p>
                    @else
                        <div class="connections-grid">
                            @foreach($section['items'] as $card)
                                @php
                                    $connection = $card['connection'];
                                    $initials = collect(explode(' ', $card['display_name']))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                @endphp
                                <article class="connection-card">
                                    <div class="connection-card-top">
                                        <a href="{{ $card['profile_route'] }}" class="connection-avatar" aria-label="Open {{ $card['display_name'] }} profile">
                                            @if($card['avatar'])
                                                <img src="{{ asset($card['avatar']) }}" alt="{{ $card['display_name'] }}">
                                            @else
                                                {{ strtoupper($initials ?: '?') }}
                                            @endif
                                        </a>
                                        <div>
                                            <h3 class="connection-name">{{ $card['display_name'] }}</h3>
                                            <div class="connection-meta">{{ $card['role'] }} · {{ '@' . $card['username'] }}</div>
                                            @if($card['location'])
                                                <div class="connection-meta"><i class="fas fa-location-dot"></i> {{ $card['location'] }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($card['message'])
                                        <div class="connection-note">{{ $card['message'] }}</div>
                                    @endif

                                    <div class="connection-actions">
                                        @if($section['id'] === 'received')
                                            <form method="POST" action="{{ route('connections.accept', $connection) }}">
                                                @csrf
                                                <button type="submit" class="connection-action connection-action-primary"><i class="fas fa-check"></i>Accept</button>
                                            </form>
                                            <form method="POST" action="{{ route('connections.decline', $connection) }}">
                                                @csrf
                                                <button type="submit" class="connection-action connection-action-secondary"><i class="fas fa-xmark"></i>Decline</button>
                                            </form>
                                        @endif

                                        @if(in_array($section['id'], ['connected', 'sent'], true))
                                            <a href="{{ route('messages.create', ['user_id' => $card['user']->id]) }}" class="connection-action connection-action-primary"><i class="fas fa-envelope"></i>Message</a>
                                        @endif

                                        @if($section['id'] === 'connected')
                                            <a href="{{ route('portfolio.galleries.index') }}" class="connection-action connection-action-secondary"><i class="fas fa-tag"></i>Tag</a>
                                        @endif

                                        @if($section['id'] !== 'blocked')
                                            <form method="POST" action="{{ route('connections.destroy', $connection) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="connection-action connection-action-secondary"><i class="fas fa-user-minus"></i>Remove</button>
                                            </form>
                                            <form method="POST" action="{{ route('connections.block', $connection) }}">
                                                @csrf
                                                <button type="submit" class="connection-action connection-action-danger"><i class="fas fa-ban"></i>Block</button>
                                            </form>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </main>
</x-app-layout>
