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
            .feed-file-control { align-items: center; border: 1px dashed #cbd5e1; border-radius: 18px; color: #475569; cursor: pointer; display: flex; gap: 12px; padding: 14px 16px; transition: border-color .2s ease, background .2s ease; }
            .feed-file-control:hover { background: #f8fafc; border-color: #111827; }
            .feed-file-control input { display: none; }
            .feed-image-preview { display: grid; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); }
            .feed-image-preview:empty { display: none; }
            .feed-image-preview img { aspect-ratio: 1 / 1; border-radius: 16px; object-fit: cover; width: 100%; }
            .feed-preview-card { align-items: stretch; border: 1px solid #e5e7eb; border-radius: 20px; display: none; grid-template-columns: 132px minmax(0, 1fr); overflow: hidden; }
            .feed-preview-card.is-visible { display: grid; }
            .feed-preview-card img, .feed-preview-placeholder { background: #f3f4f6; min-height: 108px; object-fit: cover; width: 100%; }
            .feed-preview-card strong { color: #111827; display: block; font-size: 14px; line-height: 1.35; }
            .feed-preview-card p { color: #6b7280; font-size: 12px; line-height: 1.4; margin: 5px 0 0; }
            .feed-toast { background: #050505; border-radius: 999px; bottom: 28px; box-shadow: 0 18px 45px rgba(15, 23, 42, .2); color: #fff; font-size: 14px; font-weight: 800; opacity: 0; padding: 13px 18px; pointer-events: none; position: fixed; right: 28px; transform: translateY(10px); transition: opacity .2s ease, transform .2s ease; z-index: 80; }
            .feed-toast.is-visible { opacity: 1; transform: translateY(0); }
            .feed-toast.is-error { background: #991b1b; }
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
            @media (max-width: 640px) { .feed-preview-card { grid-template-columns: 1fr; } .feed-toast { bottom: 18px; left: 18px; right: 18px; text-align: center; } }
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
                <form
                    id="create-post"
                    class="feed-card feed-create"
                    method="POST"
                    action="{{ route('feed.store') }}"
                    enctype="multipart/form-data"
                    data-feed-create-form
                    data-link-preview-url="{{ route('feed.link-preview') }}"
                >
                    @csrf
                    <textarea name="body" placeholder="Share an update. Mention members with @username.">{{ old('body') }}</textarea>
                    <div class="feed-create-grid">
                        <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="Optional link, e.g. https://asdfmodels.com/galleries/12">
                        <div class="feed-preview-card" data-feed-link-preview></div>
                        <label class="feed-file-control">
                            <i class="fas fa-images"></i>
                            <span data-feed-file-label>Add images</span>
                            <input type="file" name="images[]" accept="image/jpeg,image/jpg,image/png,image/webp" multiple data-feed-image-input>
                        </label>
                        <div class="feed-image-preview" data-feed-image-preview></div>
                        <p class="feed-muted">Emails and phone numbers are hidden automatically. External links require verification unless they point to ASDF Models.</p>
                        <button class="feed-button" type="submit" data-feed-submit><i class="fas fa-paper-plane"></i> <span data-feed-submit-label>Share Post</span></button>
                    </div>
                </form>

                <div class="mt-6 space-y-5" data-feed-posts>
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
                            <form class="feed-mention-actions" method="POST" action="{{ route('feed.mentions.update', $mention) }}" data-feed-mention-form>
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
        <div class="feed-toast" data-feed-toast></div>
    </main>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('[data-feed-create-form]');
                const toast = document.querySelector('[data-feed-toast]');
                const posts = document.querySelector('[data-feed-posts]');
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const showToast = (message, type = 'success') => {
                    if (!toast) return;
                    toast.textContent = message;
                    toast.classList.toggle('is-error', type === 'error');
                    toast.classList.add('is-visible');
                    window.setTimeout(() => toast.classList.remove('is-visible'), 3200);
                };

                if (form) {
                    const imageInput = form.querySelector('[data-feed-image-input]');
                    const imagePreview = form.querySelector('[data-feed-image-preview]');
                    const fileLabel = form.querySelector('[data-feed-file-label]');
                    const linkInput = form.querySelector('input[name="link_url"]');
                    const linkPreview = form.querySelector('[data-feed-link-preview]');
                    const submitButton = form.querySelector('[data-feed-submit]');
                    const submitLabel = form.querySelector('[data-feed-submit-label]');
                    let previewTimer = null;

                    const setBusy = (busy) => {
                        if (!submitButton) return;
                        submitButton.disabled = busy;
                        submitButton.style.opacity = busy ? '.65' : '1';
                        submitButton.style.cursor = busy ? 'wait' : '';
                        if (submitLabel) submitLabel.textContent = busy ? 'Sharing...' : 'Share Post';
                    };

                    const clearLinkPreview = () => {
                        if (!linkPreview) return;
                        linkPreview.innerHTML = '';
                        linkPreview.classList.remove('is-visible');
                    };

                    const renderLinkPreview = (preview) => {
                        if (!linkPreview || !preview) return;
                        const image = preview.image
                            ? `<img src="${preview.image}" alt="">`
                            : '<span class="feed-preview-placeholder"></span>';
                        linkPreview.innerHTML = `
                            ${image}
                            <span style="padding: 14px;">
                                <strong>${preview.title || preview.host || 'Link preview'}</strong>
                                ${preview.description ? `<p>${preview.description}</p>` : ''}
                                ${preview.host ? `<p>${preview.host}</p>` : ''}
                            </span>
                        `;
                        linkPreview.classList.add('is-visible');
                    };

                    const fetchLinkPreview = async () => {
                        const url = linkInput?.value?.trim();
                        clearLinkPreview();
                        if (!url || url.length < 4) return;

                        const params = new URLSearchParams({ url });
                        try {
                            const response = await fetch(`${form.dataset.linkPreviewUrl}?${params.toString()}`, {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });
                            const payload = await response.json();
                            if (!response.ok || !payload.success) {
                                throw new Error(payload.message || Object.values(payload.errors || {})?.[0]?.[0] || 'Unable to preview this link.');
                            }
                            renderLinkPreview(payload.preview);
                        } catch (error) {
                            showToast(error.message || 'Unable to preview this link.', 'error');
                        }
                    };

                    imageInput?.addEventListener('change', () => {
                        if (!imagePreview) return;
                        imagePreview.innerHTML = '';
                        const files = Array.from(imageInput.files || []).slice(0, 6);
                        if (fileLabel) {
                            fileLabel.textContent = files.length ? `${files.length} ${files.length === 1 ? 'image' : 'images'} selected` : 'Add images';
                        }
                        files.forEach((file) => {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            img.alt = file.name;
                            img.onload = () => URL.revokeObjectURL(img.src);
                            imagePreview.appendChild(img);
                        });
                    });

                    linkInput?.addEventListener('input', () => {
                        window.clearTimeout(previewTimer);
                        previewTimer = window.setTimeout(fetchLinkPreview, 700);
                    });

                    linkInput?.addEventListener('blur', fetchLinkPreview);

                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        setBusy(true);

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrf,
                                },
                            });
                            const payload = await response.json();
                            if (!response.ok || !payload.success) {
                                throw new Error(payload.message || Object.values(payload.errors || {})?.[0]?.[0] || 'Post could not be shared.');
                            }
                            posts?.insertAdjacentHTML('afterbegin', payload.post_html);
                            form.reset();
                            imagePreview.innerHTML = '';
                            if (fileLabel) fileLabel.textContent = 'Add images';
                            clearLinkPreview();
                            showToast(payload.message || 'Post shared.');
                        } catch (error) {
                            showToast(error.message || 'Post could not be shared.', 'error');
                        } finally {
                            setBusy(false);
                        }
                    });
                }

                document.querySelectorAll('[data-feed-mention-form]').forEach((mentionForm) => {
                    mentionForm.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        const submitter = event.submitter;
                        const formData = new FormData(mentionForm);
                        if (submitter?.name) {
                            formData.set(submitter.name, submitter.value);
                        }

                        try {
                            const response = await fetch(mentionForm.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrf,
                                },
                            });
                            const payload = await response.json();
                            if (!response.ok || !payload.success) {
                                throw new Error(payload.message || 'Mention preference could not be saved.');
                            }
                            mentionForm.closest('.feed-mention')?.remove();
                            showToast(payload.message || 'Mention preference saved.');
                        } catch (error) {
                            showToast(error.message || 'Mention preference could not be saved.', 'error');
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
