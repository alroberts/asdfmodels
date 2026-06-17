<x-app-layout>
    @push('styles')
        <style>
            .public-gallery-page {
                margin: 0 auto;
                max-width: 1240px;
                padding: 46px 24px 70px;
            }

            .public-gallery-header {
                align-items: flex-start;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                gap: 24px;
                justify-content: space-between;
                margin-bottom: 28px;
                padding-bottom: 22px;
            }

            .public-gallery-kicker {
                color: #6b7280;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .2em;
                text-transform: uppercase;
            }

            .public-gallery-title {
                color: #050505;
                font-size: clamp(30px, 5vw, 58px);
                font-weight: 850;
                line-height: .95;
                margin: 8px 0 0;
            }

            .public-gallery-description {
                color: #4b5563;
                font-size: 15px;
                line-height: 1.7;
                margin: 14px 0 0;
                max-width: 760px;
            }

            .public-gallery-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                justify-content: flex-end;
            }

            .public-gallery-pill {
                align-items: center;
                border: 1px solid #d1d5db;
                border-radius: 999px;
                color: #111827;
                display: inline-flex;
                font-size: 13px;
                font-weight: 800;
                gap: 8px;
                padding: 9px 13px;
                text-decoration: none;
            }

            .public-gallery-pill.is-primary {
                background: #050505;
                border-color: #050505;
                color: #fff;
            }

            .public-gallery-pill.is-primary:hover {
                background: #1f2937;
                border-color: #1f2937;
            }

            .public-gallery-meta {
                align-items: center;
                color: #6b7280;
                display: flex;
                flex-wrap: wrap;
                font-size: 13px;
                font-weight: 800;
                gap: 12px;
                margin-top: 16px;
                text-transform: uppercase;
            }

            .public-gallery-credits {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 16px;
            }

            .public-gallery-credit-label {
                color: #64748b;
                font-size: 12px;
                font-weight: 850;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            .public-gallery-credit-pill {
                align-items: center;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 999px;
                color: #111827;
                display: inline-flex;
                font-size: 13px;
                font-weight: 800;
                gap: 7px;
                padding: 8px 11px;
                text-decoration: none;
            }

            .public-gallery-grid {
                display: grid;
                gap: 14px;
                grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            }

            .public-gallery-image {
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
                border: 1px solid #e5e7eb;
                border-radius: 18px;
                cursor: zoom-in;
                overflow: hidden;
                padding: 0;
            }

            .public-gallery-image img {
                display: block;
                height: 100%;
                object-fit: cover;
                transition: transform 220ms ease;
                width: 100%;
            }

            .public-gallery-image:hover img {
                transform: scale(1.04);
            }

            .public-gallery-empty {
                border: 1px dashed #d1d5db;
                border-radius: 18px;
                color: #6b7280;
                padding: 56px 20px;
                text-align: center;
            }

            .public-gallery-lightbox {
                background: rgba(8, 13, 23, .92);
                display: none;
                inset: 0;
                position: fixed;
                z-index: 90;
            }

            .public-gallery-lightbox.is-open {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 360px;
            }

            .public-gallery-stage {
                align-items: center;
                display: flex;
                justify-content: center;
                min-height: 100vh;
                padding: 34px 84px;
                position: relative;
            }

            .public-gallery-stage img {
                border-radius: 12px;
                max-height: 90vh;
                max-width: 100%;
                object-fit: contain;
            }

            .public-gallery-close,
            .public-gallery-nav {
                align-items: center;
                background: rgba(255, 255, 255, .12);
                border: 1px solid rgba(255, 255, 255, .18);
                border-radius: 999px;
                color: #fff;
                display: inline-flex;
                justify-content: center;
                position: absolute;
                transition: background .18s ease, transform .18s ease;
            }

            .public-gallery-close:hover,
            .public-gallery-nav:hover {
                background: rgba(255, 255, 255, .22);
                transform: translateY(-1px);
            }

            .public-gallery-close {
                font-size: 24px;
                height: 44px;
                right: 22px;
                top: 22px;
                width: 44px;
                z-index: 2;
            }

            .public-gallery-nav {
                font-size: 28px;
                height: 58px;
                top: 50%;
                transform: translateY(-50%);
                width: 58px;
            }

            .public-gallery-nav:hover {
                transform: translateY(-50%) translateY(-1px);
            }

            .public-gallery-prev {
                left: 20px;
            }

            .public-gallery-next {
                right: 20px;
            }

            .public-gallery-sidebar {
                background: #fff;
                display: flex;
                flex-direction: column;
                max-height: 100vh;
                min-height: 100vh;
                overflow: hidden;
            }

            .public-gallery-sidebar-head,
            .public-gallery-sidebar-section,
            .public-gallery-comment-form {
                border-bottom: 1px solid #e5e7eb;
                padding: 18px;
            }

            .public-gallery-sidebar-head h2 {
                color: #111827;
                font-size: 18px;
                font-weight: 850;
                margin: 0;
            }

            .public-gallery-sidebar-head p {
                color: #64748b;
                font-size: 13px;
                margin: 5px 0 0;
            }

            .public-gallery-sidebar-body {
                flex: 1;
                overflow-y: auto;
            }

            .public-gallery-sidebar-kicker {
                color: #64748b;
                font-size: 11px;
                font-weight: 850;
                letter-spacing: .16em;
                margin: 0 0 12px;
                text-transform: uppercase;
            }

            .public-gallery-tag-list,
            .public-gallery-comment-list {
                display: grid;
                gap: 10px;
            }

            .public-gallery-tag {
                align-items: center;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                display: flex;
                gap: 10px;
                padding: 10px;
            }

            .public-gallery-tag-icon {
                align-items: center;
                background: #111827;
                border-radius: 999px;
                color: #fff;
                display: inline-flex;
                height: 34px;
                justify-content: center;
                width: 34px;
            }

            .public-gallery-tag strong,
            .public-gallery-comment strong {
                color: #111827;
                display: block;
                font-size: 13px;
            }

            .public-gallery-tag span,
            .public-gallery-comment span {
                color: #64748b;
                display: block;
                font-size: 12px;
                margin-top: 2px;
                text-transform: capitalize;
            }

            .public-gallery-comment {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 10px;
            }

            .public-gallery-comment p {
                color: #334155;
                font-size: 13px;
                line-height: 1.5;
                margin: 6px 0 0;
                white-space: pre-wrap;
            }

            .public-gallery-comment-form textarea {
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                min-height: 86px;
                padding: 10px 12px;
                resize: vertical;
                width: 100%;
            }

            .public-gallery-sidebar-button {
                align-items: center;
                background: #050505;
                border: 1px solid #050505;
                border-radius: 999px;
                color: #fff;
                display: inline-flex;
                font-size: 13px;
                font-weight: 850;
                gap: 8px;
                justify-content: center;
                margin-top: 10px;
                padding: 10px 13px;
                width: 100%;
            }

            .public-gallery-sidebar-button.secondary {
                background: #fff;
                border-color: #cbd5e1;
                color: #111827;
            }

            .public-gallery-sidebar-status {
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
                margin-top: 8px;
            }

            @media (max-width: 720px) {
                .public-gallery-header {
                    flex-direction: column;
                }

                .public-gallery-actions {
                    justify-content: flex-start;
                }

                .public-gallery-lightbox.is-open {
                    grid-template-columns: 1fr;
                    overflow-y: auto;
                }

                .public-gallery-stage {
                    min-height: 62vh;
                    padding: 72px 18px 24px;
                }

                .public-gallery-sidebar {
                    max-height: none;
                    min-height: auto;
                }

                .public-gallery-nav {
                    height: 44px;
                    width: 44px;
                }
            }
        </style>
    @endpush

    @php
        $imageType = $gallery->owner_role === 'photographer' ? 'photographer' : 'model';
        $viewer = auth()->user();
        $ownerCanManage = $viewer && (int) $viewer->id === (int) $gallery->user_id;
        $canInteract = $viewer && (int) $viewer->id !== (int) $gallery->user_id;
        $galleryImagesPayload = $images->values()->map(function ($image) {
            return [
                'id' => $image->id,
                'title' => $image->title ?: 'Gallery image',
                'thumbnail' => asset($image->thumbnail_path ?? $image->full_path),
                'full' => asset($image->full_path ?? $image->thumbnail_path),
                'credits' => $image->credits
                    ->where('status', \App\Models\PortfolioCredit::STATUS_ACCEPTED_VISIBLE)
                    ->values()
                    ->map(fn ($credit) => [
                        'name' => $credit->creditedUser?->display_name ?: $credit->creditedUser?->name,
                        'role' => $credit->credited_role,
                        'url' => $credit->creditedUser
                            ? ($credit->creditedUser->is_photographer ? route('photographers.show', $credit->creditedUser->profileRouteIdentifier()) : route('models.show', $credit->creditedUser->profileRouteIdentifier()))
                            : null,
                    ])
                    ->values(),
                'comments' => $image->comments
                    ->where('is_hidden', false)
                    ->values()
                    ->map(fn ($comment) => [
                        'id' => $comment->id,
                        'user' => $comment->user?->display_name ?: $comment->user?->name,
                        'body' => $comment->body,
                        'created_at' => $comment->created_at->diffForHumans(),
                    ])
                    ->values(),
            ];
        });
    @endphp

    <main class="public-gallery-page">
        <header class="public-gallery-header">
            <div>
                <p class="public-gallery-kicker">{{ $ownerProfile?->display_name ?? $owner->display_name }}</p>
                <h1 class="public-gallery-title">{{ $gallery->name }}</h1>
                @if($gallery->description)
                    <p class="public-gallery-description">{{ $gallery->description }}</p>
                @endif
                <div class="public-gallery-meta">
                    <span><i class="fas fa-images"></i> {{ $images->count() }} {{ $images->count() === 1 ? 'image' : 'images' }}</span>
                    <span><i class="fas fa-eye"></i> {{ ucfirst($gallery->visibility ?? 'public') }}</span>
                    <span><i class="fas {{ $gallery->contains_nudity ? 'fa-triangle-exclamation' : 'fa-shield-heart' }}"></i> {{ $gallery->contains_nudity ? 'NSFW Content' : 'Standard Content' }}</span>
                </div>
                @if(($galleryCredits ?? collect())->isNotEmpty())
                    <div class="public-gallery-credits">
                        <span class="public-gallery-credit-label">Credits</span>
                        @foreach($galleryCredits as $credit)
                            @php
                                $creditedUser = $credit->creditedUser;
                                $creditRoute = $creditedUser?->is_photographer
                                    ? route('photographers.show', $creditedUser->profileRouteIdentifier())
                                    : route('models.show', $creditedUser->profileRouteIdentifier());
                            @endphp
                            @if($creditedUser)
                                <a href="{{ $creditRoute }}" class="public-gallery-credit-pill">
                                    <i class="fas fa-user-tag"></i>
                                    <span>{{ $creditedUser->display_name ?: $creditedUser->name }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="public-gallery-actions">
                @if($ownerCanManage)
                    <a href="{{ route('portfolio.galleries.show', $gallery->id) }}" class="public-gallery-pill is-primary">
                        <i class="fas fa-sliders"></i>
                        <span>Manage Gallery</span>
                    </a>
                @endif
                <a href="{{ $ownerProfileRoute }}" class="public-gallery-pill">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Profile</span>
                </a>
            </div>
        </header>

        @if($images->isNotEmpty())
            <section class="public-gallery-grid">
                @foreach($images as $index => $image)
                    <button type="button" class="public-gallery-image" onclick="openPublicGalleryViewer({{ $index }})">
                        <img src="{{ asset($image->thumbnail_path ?? $image->full_path) }}" alt="{{ $image->title ?? $gallery->name }}">
                    </button>
                @endforeach
            </section>
        @else
            <section class="public-gallery-empty">
                <i class="fas fa-images text-3xl"></i>
                <p class="mt-3 text-sm font-bold">This gallery has no public images yet.</p>
            </section>
        @endif
    </main>

    <div id="publicGalleryLightbox" class="public-gallery-lightbox" aria-hidden="true">
        <div class="public-gallery-stage">
            <button type="button" class="public-gallery-close" onclick="closePublicGalleryLightbox()" aria-label="Close gallery viewer">&times;</button>
            <button type="button" class="public-gallery-nav public-gallery-prev" onclick="movePublicGalleryImage(-1)" aria-label="Previous image"><i class="fas fa-chevron-left"></i></button>
            <img id="publicGalleryLightboxImage" src="" alt="">
            <button type="button" class="public-gallery-nav public-gallery-next" onclick="movePublicGalleryImage(1)" aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
        </div>
        <aside class="public-gallery-sidebar">
            <div class="public-gallery-sidebar-head">
                <h2 id="publicGallerySidebarTitle">Gallery image</h2>
                <p><span id="publicGalleryCounter">1 of {{ $images->count() }}</span></p>
            </div>
            <div class="public-gallery-sidebar-body">
                <section class="public-gallery-sidebar-section">
                    <p class="public-gallery-sidebar-kicker">Tagged</p>
                    <div id="publicGalleryTags" class="public-gallery-tag-list"></div>
                    @if($canInteract)
                        <button type="button" class="public-gallery-sidebar-button secondary" onclick="requestPublicGalleryTag()">
                            <i class="fas fa-user-plus"></i>
                            <span>Request tag</span>
                        </button>
                    @elseif(!auth()->check())
                        <a href="{{ route('login') }}" class="public-gallery-sidebar-button secondary">
                            <i class="fas fa-right-to-bracket"></i>
                            <span>Log in to request tag</span>
                        </a>
                    @endif
                    <div id="publicGalleryTagStatus" class="public-gallery-sidebar-status"></div>
                </section>

                <section class="public-gallery-sidebar-section">
                    <p class="public-gallery-sidebar-kicker">Comments</p>
                    <div id="publicGalleryComments" class="public-gallery-comment-list"></div>
                </section>
            </div>

            @auth
                <form class="public-gallery-comment-form" onsubmit="submitPublicGalleryComment(event)">
                    <textarea id="publicGalleryCommentBody" placeholder="Add a comment..."></textarea>
                    <button type="submit" class="public-gallery-sidebar-button">
                        <i class="fas fa-paper-plane"></i>
                        <span>Add comment</span>
                    </button>
                    <div id="publicGalleryCommentStatus" class="public-gallery-sidebar-status"></div>
                </form>
            @else
                <div class="public-gallery-comment-form">
                    <a href="{{ route('login') }}" class="public-gallery-sidebar-button">
                        <i class="fas fa-right-to-bracket"></i>
                        <span>Log in to comment</span>
                    </a>
                </div>
            @endauth
        </aside>
    </div>

    <script>
        const publicGalleryImages = @js($galleryImagesPayload);
        const publicGalleryImageType = @js($imageType);
        let publicGalleryIndex = 0;

        function openPublicGalleryViewer(index) {
            publicGalleryIndex = index;
            renderPublicGalleryViewer();
            document.getElementById('publicGalleryLightbox').classList.add('is-open');
            document.getElementById('publicGalleryLightbox').setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closePublicGalleryLightbox() {
            document.getElementById('publicGalleryLightbox').classList.remove('is-open');
            document.getElementById('publicGalleryLightbox').setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function movePublicGalleryImage(direction) {
            publicGalleryIndex = (publicGalleryIndex + direction + publicGalleryImages.length) % publicGalleryImages.length;
            renderPublicGalleryViewer();
        }

        function renderPublicGalleryViewer() {
            const image = publicGalleryImages[publicGalleryIndex];
            if (!image) return;

            document.getElementById('publicGalleryLightboxImage').src = image.full;
            document.getElementById('publicGallerySidebarTitle').textContent = image.title || 'Gallery image';
            document.getElementById('publicGalleryCounter').textContent = `${publicGalleryIndex + 1} of ${publicGalleryImages.length}`;
            document.getElementById('publicGalleryCommentBody') && (document.getElementById('publicGalleryCommentBody').value = '');
            document.getElementById('publicGalleryTagStatus').textContent = '';
            document.getElementById('publicGalleryCommentStatus') && (document.getElementById('publicGalleryCommentStatus').textContent = '');

            renderPublicGalleryTags(image.credits || []);
            renderPublicGalleryComments(image.comments || []);
        }

        function renderPublicGalleryTags(credits) {
            const container = document.getElementById('publicGalleryTags');
            container.innerHTML = '';

            if (credits.length === 0) {
                container.innerHTML = '<p class="public-gallery-sidebar-status">No one is tagged in this image yet.</p>';
                return;
            }

            credits.forEach((credit) => {
                const item = document.createElement(credit.url ? 'a' : 'div');
                if (credit.url) item.href = credit.url;
                item.className = 'public-gallery-tag';
                item.innerHTML = `
                    <span class="public-gallery-tag-icon"><i class="fas fa-user-tag"></i></span>
                    <span>
                        <strong>${escapePublicGalleryHtml(credit.name || 'Member')}</strong>
                        <span>${escapePublicGalleryHtml(credit.role || 'credit')}</span>
                    </span>
                `;
                container.appendChild(item);
            });
        }

        function renderPublicGalleryComments(comments) {
            const container = document.getElementById('publicGalleryComments');
            container.innerHTML = '';

            if (comments.length === 0) {
                container.innerHTML = '<p class="public-gallery-sidebar-status">No comments yet.</p>';
                return;
            }

            comments.forEach((comment) => {
                const item = document.createElement('article');
                item.className = 'public-gallery-comment';
                item.innerHTML = `
                    <strong>${escapePublicGalleryHtml(comment.user || 'Member')}</strong>
                    <span>${escapePublicGalleryHtml(comment.created_at || '')}</span>
                    <p>${escapePublicGalleryHtml(comment.body || '')}</p>
                `;
                container.appendChild(item);
            });
        }

        async function requestPublicGalleryTag() {
            const image = publicGalleryImages[publicGalleryIndex];
            const status = document.getElementById('publicGalleryTagStatus');
            status.textContent = 'Sending request...';

            const response = await fetch('{{ route('portfolio.credits.request') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    image_type: publicGalleryImageType,
                    image_id: image.id
                })
            });

            const payload = await response.json().catch(() => ({}));
            status.textContent = response.ok ? (payload.message || 'Tag request sent.') : (payload.message || 'Could not send tag request.');
        }

        async function submitPublicGalleryComment(event) {
            event.preventDefault();
            const image = publicGalleryImages[publicGalleryIndex];
            const body = document.getElementById('publicGalleryCommentBody').value.trim();
            const status = document.getElementById('publicGalleryCommentStatus');

            if (!body) {
                status.textContent = 'Write a comment first.';
                return;
            }

            status.textContent = 'Saving...';
            const response = await fetch('{{ route('public.galleries.comments.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    image_type: publicGalleryImageType,
                    image_id: image.id,
                    body
                })
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                status.textContent = payload.message || 'Could not add comment.';
                return;
            }

            image.comments.push(payload.comment);
            document.getElementById('publicGalleryCommentBody').value = '';
            status.textContent = payload.message || 'Comment added.';
            renderPublicGalleryComments(image.comments);
        }

        document.addEventListener('keydown', (event) => {
            const viewerOpen = document.getElementById('publicGalleryLightbox').classList.contains('is-open');
            if (!viewerOpen) return;

            if (event.key === 'Escape') closePublicGalleryLightbox();
            if (event.key === 'ArrowLeft') movePublicGalleryImage(-1);
            if (event.key === 'ArrowRight') movePublicGalleryImage(1);
        });

        function escapePublicGalleryHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }
    </script>
</x-app-layout>
