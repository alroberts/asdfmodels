<x-app-layout>
    <x-slot name="header">
        <div style="align-items:center;display:flex;gap:18px;justify-content:space-between;">
            <div>
                <p style="color:#6b7280;font-size:12px;font-weight:900;letter-spacing:.28em;margin:0 0 6px;text-transform:uppercase;">Feed Post</p>
                <h1 style="color:#050505;font-size:clamp(28px,4vw,48px);font-weight:900;letter-spacing:-.045em;line-height:1;margin:0;">Mention Review</h1>
            </div>
            <a href="{{ route('dashboard') }}" style="border:1px solid #d1d5db;border-radius:999px;color:#111827;font-weight:800;padding:10px 16px;text-decoration:none;">Back to Feed</a>
        </div>
    </x-slot>

    @push('styles')
        <style>
            .feed-focus-shell {
                display: grid;
                gap: 22px;
                margin: 0 auto;
                max-width: 820px;
                padding: 38px 20px 72px;
            }

            .feed-review-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 24px;
                box-shadow: 0 18px 45px rgba(15, 23, 42, .06);
                padding: 22px;
            }

            .feed-review-card h2 {
                color: #111827;
                font-size: 22px;
                font-weight: 900;
                letter-spacing: -.025em;
                margin: 0 0 8px;
            }

            .feed-review-card p {
                color: #6b7280;
                font-size: 14px;
                line-height: 1.6;
                margin: 0;
            }

            .feed-review-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 18px;
            }

            .feed-review-actions button {
                border: 1px solid #d1d5db;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 850;
                padding: 10px 14px;
            }

            .feed-review-actions button.primary {
                background: #050505;
                border-color: #050505;
                color: #fff;
            }
        </style>
    @endpush

    <main class="feed-focus-shell">
        @if($pendingMention)
            <section class="feed-review-card">
                <h2>You were mentioned in this post</h2>
                <p>Choose whether this tag can appear on your profile feed. You can accept the mention privately, show it publicly on your feed, or reject it.</p>
                <form class="feed-review-actions" method="POST" action="{{ route('feed.mentions.update', $pendingMention) }}">
                    @csrf
                    @method('PATCH')
                    <button class="primary" name="status" value="accepted_visible" type="submit">Accept + show on profile</button>
                    <button name="status" value="accepted_hidden" type="submit">Accept only</button>
                    <button name="status" value="rejected" type="submit">Reject</button>
                </form>
            </section>
        @endif

        @include('feed.partials.post-card', ['post' => $post])
    </main>
</x-app-layout>
