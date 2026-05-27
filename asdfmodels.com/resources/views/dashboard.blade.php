<x-app-layout>
    <x-slot name="header">
        <div class="feed-header">
            <div>
                <p class="feed-kicker">Member Feed</p>
                <h1>Latest from your connections</h1>
                <p>Share updates, new work, links, and tagged collaborations.</p>
            </div>
            <a href="#create-post" class="feed-primary-action"><i class="fas fa-plus"></i> Create Post</a>
        </div>
    </x-slot>

    @push('styles')
        <style>
            .feed-shell { max-width: 1120px; margin: 0 auto; padding: 38px 20px 72px; }
            .feed-header { align-items: center; display: flex; justify-content: space-between; gap: 24px; }
            .feed-header h1 { color: #050505; font-size: clamp(30px, 4vw, 54px); font-weight: 900; letter-spacing: -0.055em; line-height: .92; margin: 4px 0 10px; }
            .feed-header p { color: #5b6472; margin: 0; }
            .feed-kicker { color: #6b7280; font-size: 12px; font-weight: 900; letter-spacing: .32em; text-transform: uppercase; }
            .feed-primary-action, .feed-button { align-items: center; background: #050505; border: 1px solid #050505; border-radius: 999px; color: #fff; display: inline-flex; font-weight: 850; gap: 9px; padding: 12px 18px; text-decoration: none; }
            .feed-layout { display: grid; gap: 24px; grid-template-columns: minmax(0, 1fr) 320px; }
            .feed-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 26px; box-shadow: 0 18px 55px rgba(15, 23, 42, .06); overflow: hidden; }
            .feed-create { padding: 22px; }
            .feed-create textarea, .feed-create input[type="url"] { border: 1px solid #d1d5db; border-radius: 18px; color: #111827; display: block; font-size: 15px; padding: 14px 16px; width: 100%; }
            .feed-create textarea { min-height: 118px; resize: vertical; }
            .feed-create-grid { display: grid; gap: 14px; margin-top: 14px; }
            .feed-muted { color: #6b7280; font-size: 13px; line-height: 1.5; }
            .feed-post { padding: 22px; }
            .feed-author { align-items: center; display: flex; gap: 12px; }
            .feed-avatar { align-items: center; background: #111827; border-radius: 999px; color: #fff; display: flex; font-weight: 900; height: 44px; justify-content: center; overflow: hidden; width: 44px; }
            .feed-avatar img { height: 100%; object-fit: cover; width: 100%; }
            .feed-author strong { color: #0f172a; display: block; font-size: 15px; }
            .feed-author span { color: #6b7280; display: block; font-size: 12px; margin-top: 2px; }
            .feed-body { color: #1f2937; font-size: 15px; line-height: 1.65; margin: 16px 0 0; white-space: pre-line; }
            .feed-images { display: grid; gap: 8px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 16px; }
            .feed-images img { aspect-ratio: 1 / 1; border-radius: 18px; object-fit: cover; width: 100%; }
            .feed-link-card { align-items: stretch; border: 1px solid #e5e7eb; border-radius: 20px; display: grid; grid-template-columns: 150px minmax(0, 1fr); margin-top: 16px; overflow: hidden; text-decoration: none; }
            .feed-link-card img, .feed-link-placeholder { background: #f3f4f6; height: 100%; min-height: 126px; object-fit: cover; width: 100%; }
            .feed-link-body { padding: 14px; }
            .feed-link-body strong { color: #111827; display: block; font-size: 15px; }
            .feed-link-body p { color: #6b7280; font-size: 13px; line-height: 1.45; margin: 6px 0 0; }
            .feed-panel { padding: 20px; }
            .feed-panel h2 { color: #111827; font-size: 18px; font-weight: 900; margin: 0 0 10px; }
            .feed-mention { border-top: 1px solid #e5e7eb; padding: 14px 0; }
            .feed-mention:first-of-type { border-top: 0; }
            .feed-mention-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
            .feed-mention-actions button { border: 1px solid #d1d5db; border-radius: 999px; font-size: 12px; font-weight: 850; padding: 8px 10px; }
            .feed-empty { color: #6b7280; padding: 42px 22px; text-align: center; }
            @media (max-width: 900px) { .feed-header, .feed-layout { display: block; } .feed-primary-action { margin-top: 18px; } .feed-card { margin-top: 18px; } }
        </style>
    @endpush

    <main class="feed-shell">
        @if(session('status'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="feed-layout">
            <section>
                <form id="create-post" class="feed-card feed-create" method="POST" action="{{ route('feed.store') }}" enctype="multipart/form-data">
                    @csrf
                    <textarea name="body" placeholder="Share an update. Mention members with @username.">{{ old('body') }}</textarea>
                    <div class="feed-create-grid">
                        <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="Optional link, e.g. https://asdfmodels.com/galleries/12">
                        <input type="file" name="images[]" accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
                        <p class="feed-muted">Emails and phone numbers are hidden automatically. External links require verification unless they point to ASDF Models.</p>
                        <button class="feed-button" type="submit"><i class="fas fa-paper-plane"></i> Share Post</button>
                    </div>
                </form>

                <div class="mt-6 space-y-5">
                    @forelse($posts as $post)
                        @include('feed.partials.post-card', ['post' => $post])
                    @empty
                        <div class="feed-card feed-empty">
                            <strong>No posts yet.</strong>
                            <p class="mt-2">Connect with members or create the first post in your feed.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $posts->links() }}</div>
            </section>

            <aside>
                <div class="feed-card feed-panel">
                    <h2>Pending Mentions</h2>
                    <p class="feed-muted">Choose whether tagged posts can appear on your profile feed.</p>
                    @forelse($pendingMentions as $mention)
                        <div class="feed-mention">
                            <strong>{{ $mention->mentionedBy?->display_name ?: $mention->mentionedBy?->name }}</strong>
                            <p class="feed-muted">Mentioned you in a post.</p>
                            <form class="feed-mention-actions" method="POST" action="{{ route('feed.mentions.update', $mention) }}">
                                @csrf
                                @method('PATCH')
                                <button name="status" value="accepted_visible" type="submit">Accept + show</button>
                                <button name="status" value="accepted_hidden" type="submit">Accept only</button>
                                <button name="status" value="rejected" type="submit">Reject</button>
                            </form>
                        </div>
                    @empty
                        <p class="feed-muted">Nothing waiting right now.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </main>
</x-app-layout>
