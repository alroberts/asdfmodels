<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
        <style>
            .profile-photo-cropper .cropper-view-box,
            .profile-photo-cropper .cropper-face {
                border-radius: 9999px;
            }

            .profile-photo-cropper .cropper-container {
                max-width: 100%;
            }

            .profile-photo-cropper {
                height: 420px;
                margin: 0 auto;
                max-width: 420px;
                overflow: hidden;
                width: min(420px, 100%);
            }

            .profile-photo-cropper > img {
                display: block;
                max-height: 420px;
                max-width: 100%;
            }

            .profile-photo-cropper .cropper-container {
                height: 420px !important;
                max-height: 420px;
                max-width: 420px;
                width: 420px !important;
            }

            .profile-photo-cropper .cropper-wrap-box,
            .profile-photo-cropper .cropper-canvas,
            .profile-photo-cropper .cropper-drag-box {
                height: 420px !important;
                max-height: 420px;
                max-width: 420px;
                width: 420px !important;
            }

            .profile-media-crop-card {
                margin: 0 auto;
                max-width: 560px;
            }

            .cover-photo-cropper {
                height: 360px;
                margin: 0 auto;
                max-width: 900px;
                overflow: hidden;
                width: min(900px, 100%);
            }

            .cover-photo-cropper > img {
                display: block;
                max-height: 360px;
                max-width: 100%;
            }

            .profile-media-current {
                align-items: center;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                min-height: 72px;
            }

            .profile-media-current-copy {
                min-width: 0;
            }

            .profile-media-current-thumb {
                border-radius: 9999px;
                flex: 0 0 56px;
                height: 56px;
                overflow: hidden;
                width: 56px;
            }

            .profile-media-current-thumb img {
                display: block;
                height: 56px;
                object-fit: cover;
                width: 56px;
            }

            .profile-media-current-placeholder {
                align-items: center;
                display: flex;
                height: 56px;
                justify-content: center;
                width: 56px;
            }

            .model-profile-layout {
                align-items: start;
                display: grid;
                gap: 24px;
                grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
            }

            .model-profile-main,
            .model-profile-side {
                display: grid;
                gap: 24px;
                min-width: 0;
            }

            .model-profile-side {
                position: sticky;
                top: 96px;
            }

            .model-profile-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1), 0 1px 2px rgba(15, 23, 42, 0.06);
                padding: 28px;
            }

            .photographer-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1), 0 1px 2px rgba(15, 23, 42, 0.06);
                padding: 28px;
            }

            .photographer-card-header {
                align-items: flex-end;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                margin-bottom: 20px;
            }

            .photographer-muted-icon {
                color: #d1d5db;
            }

            .photographer-kicker {
                color: #6b7280;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: 0.2em;
                margin: 0;
                text-transform: uppercase;
            }

            .photographer-heading {
                color: #050505;
                font-size: 24px;
                font-weight: 800;
                margin: 4px 0 0;
            }

            .profile-inline-actions {
                align-items: center;
                display: flex;
                gap: 10px;
                justify-content: space-between;
            }

            .profile-icon-action,
            .profile-text-action {
                align-items: center;
                border: 1px solid #d1d5db;
                background: #fff;
                color: #111827;
                cursor: pointer;
                display: inline-flex;
                font-weight: 700;
                gap: 8px;
                text-decoration: none;
                transition: border-color .15s ease, background .15s ease, color .15s ease;
            }

            .profile-icon-action {
                border-radius: 999px;
                height: 38px;
                justify-content: center;
                width: 38px;
            }

            .profile-text-action {
                border-radius: 999px;
                font-size: 13px;
                padding: 9px 13px;
            }

            .profile-icon-action:hover,
            .profile-text-action:hover {
                border-color: #111827;
                background: #111827;
                color: #fff;
            }

            .connection-popover {
                position: relative;
                z-index: 70;
            }

            .connection-popover > summary {
                list-style: none;
            }

            .connection-popover > summary::-webkit-details-marker {
                display: none;
            }

            .connection-popover-panel {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 18px;
                box-shadow: 0 22px 60px rgba(15, 23, 42, .2);
                margin-top: 10px;
                padding: 16px;
                position: absolute;
                right: 0;
                top: 100%;
                width: min(320px, calc(100vw - 32px));
                z-index: 80;
            }

            .model-hero-content {
                align-items: stretch;
                display: grid;
                gap: 24px;
                grid-template-columns: auto minmax(0, 1fr) minmax(220px, auto);
            }

            .model-hero-details {
                padding-top: 14px;
            }

            .model-hero-action-column {
                align-items: flex-end;
                display: flex;
                flex-direction: column;
                gap: 16px;
                justify-content: space-between;
                padding-top: 22px;
            }

            .model-hero-socials,
            .model-hero-primary-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .profile-bio-display {
                color: #374151;
                font-size: 16px;
                line-height: 1.75;
                max-width: 64rem;
                white-space: pre-line;
            }

            .profile-bio-empty {
                border: 1px dashed #d1d5db;
                border-radius: 14px;
                color: #6b7280;
                padding: 18px;
            }

            .profile-inline-editor {
                display: none;
                margin-top: 18px;
            }

            .profile-inline-editor.is-open {
                display: block;
            }

            .profile-inline-editor textarea {
                border: 1px solid #9ca3af;
                border-radius: 14px;
                color: #111827;
                display: block;
                font: inherit;
                line-height: 1.6;
                min-height: 180px;
                padding: 14px 16px;
                resize: vertical;
                width: 100%;
            }

            .profile-inline-editor textarea:focus {
                border-color: #111827;
                box-shadow: 0 0 0 3px rgba(17, 24, 39, .12);
                outline: none;
            }

            .profile-inline-help {
                color: #6b7280;
                font-size: 13px;
                margin-top: 8px;
            }

            .profile-inline-buttons {
                align-items: center;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                margin-top: 14px;
            }

            .profile-inline-save,
            .profile-inline-cancel {
                border-radius: 10px;
                cursor: pointer;
                font-weight: 800;
                padding: 10px 16px;
            }

            .profile-inline-save {
                background: #111827;
                border: 1px solid #111827;
                color: #fff;
            }

            .profile-inline-cancel {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #374151;
            }

            .profile-inline-status {
                color: #166534;
                font-size: 13px;
                font-weight: 700;
                margin-right: auto;
            }

            .profile-inline-status.is-error {
                color: #b91c1c;
            }

            .profile-chip-list {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .profile-chip {
                background: #f3f4f6;
                border: 1px solid #e5e7eb;
                border-radius: 999px;
                color: #374151;
                display: inline-flex;
                font-size: 13px;
                font-weight: 800;
                padding: 7px 11px;
            }

            .profile-section-empty {
                border: 1px dashed #d1d5db;
                border-radius: 14px;
                color: #6b7280;
                font-size: 14px;
                line-height: 1.6;
                padding: 16px;
            }

            .model-snapshot-action {
                align-items: center;
                border: 1px solid rgba(255, 255, 255, .2);
                border-radius: 999px;
                color: #fff;
                display: inline-flex;
                font-size: 12px;
                font-weight: 800;
                gap: 7px;
                padding: 8px 11px;
                text-decoration: none;
                transition: background .15s ease, border-color .15s ease;
            }

            .model-snapshot-action:hover {
                background: rgba(255, 255, 255, .12);
                border-color: rgba(255, 255, 255, .45);
            }

            .quick-edit-overlay {
                align-items: center;
                background: rgba(3, 7, 18, .72);
                bottom: 0;
                display: none;
                justify-content: center;
                left: 0;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 24px;
                position: fixed;
                right: 0;
                top: 0;
                z-index: 95;
            }

            .quick-edit-overlay.is-open {
                display: flex;
            }

            .quick-edit-modal {
                background: #fff;
                border-radius: 18px;
                box-shadow: 0 24px 80px rgba(15, 23, 42, .32);
                display: flex;
                flex-direction: column;
                max-height: calc(100dvh - 48px);
                max-width: 760px;
                overflow: hidden;
                width: min(100%, 760px);
            }

            .quick-edit-header,
            .quick-edit-footer {
                align-items: center;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                padding: 20px 24px;
            }

            .quick-edit-footer {
                border-bottom: 0;
                border-top: 1px solid #e5e7eb;
            }

            .quick-edit-body {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 24px;
            }

            .quick-edit-grid {
                display: grid;
                gap: 16px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quick-edit-field label,
            .quick-edit-checklist-label {
                color: #374151;
                display: block;
                font-size: 13px;
                font-weight: 800;
                margin-bottom: 7px;
            }

            .quick-edit-field input,
            .quick-edit-field select {
                border: 1px solid #9ca3af;
                border-radius: 10px;
                display: block;
                padding: 10px 12px;
                width: 100%;
            }

            .quick-edit-checklist {
                display: grid;
                gap: 10px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quick-edit-check {
                align-items: center;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                display: flex;
                gap: 9px;
                padding: 10px 12px;
            }

            .quick-edit-message {
                color: #166534;
                font-size: 13px;
                font-weight: 800;
                margin-right: auto;
            }

            .quick-edit-message.is-error {
                color: #b91c1c;
            }

            @media (max-width: 720px) {
                .quick-edit-overlay {
                    align-items: flex-start;
                    padding: 12px;
                }

                .quick-edit-modal {
                    max-height: calc(100dvh - 24px);
                    width: 100%;
                }

                .quick-edit-grid,
                .quick-edit-checklist {
                    grid-template-columns: 1fr;
                }
            }

            .model-profile-card-dashed {
                border: 1px dashed #d1d5db;
            }

            .model-profile-card-header {
                align-items: flex-end;
                display: flex;
                gap: 16px;
                justify-content: space-between;
                margin-bottom: 20px;
            }

            .model-gallery-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }

            .model-gallery-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                color: inherit;
                display: block;
                min-width: 0;
                overflow: hidden;
                text-decoration: none;
                transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
            }

            .model-gallery-card:hover {
                border-color: #111827;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.1);
                transform: translateY(-1px);
            }

            .model-gallery-thumb {
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
                overflow: hidden;
            }

            .model-gallery-thumb img {
                display: block;
                height: 100%;
                object-fit: cover;
                transition: transform 220ms ease;
                width: 100%;
            }

            .model-gallery-card:hover .model-gallery-thumb img {
                transform: scale(1.04);
            }

            .model-gallery-body {
                padding: 12px;
            }

            .model-view-all-link {
                align-items: center;
                border: 1px solid #d1d5db;
                border-radius: 999px;
                color: #111827;
                display: inline-flex;
                font-size: 13px;
                font-weight: 700;
                gap: 8px;
                padding: 8px 12px;
                text-decoration: none;
                transition: border-color 160ms ease, background 160ms ease;
            }

            .model-view-all-link:hover {
                background: #f9fafb;
                border-color: #111827;
            }

            .model-featured-grid {
                display: grid;
                gap: 16px;
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .photographer-featured-grid,
            .photographer-tagged-grid {
                display: grid;
                gap: 12px;
            }

            .photographer-featured-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .photographer-tagged-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .photographer-image-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                color: inherit;
                display: block;
                min-width: 0;
                overflow: hidden;
                padding: 0;
                text-align: left;
                text-decoration: none;
            }

            .photographer-square {
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
                overflow: hidden;
                position: relative;
            }

            .photographer-square img {
                display: block;
                height: 100%;
                object-fit: cover;
                transition: transform 220ms ease;
                width: 100%;
            }

            .photographer-square:hover img {
                transform: scale(1.04);
            }

            .photographer-empty {
                border: 1px dashed #d1d5db;
                border-radius: 14px;
                color: #6b7280;
                padding: 32px 18px;
                text-align: center;
            }

            .model-snapshot-card {
                background: #050505;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16), 0 1px 2px rgba(15, 23, 42, 0.08);
                color: #fff;
                overflow: hidden;
            }

            .model-snapshot-header {
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 20px;
            }

            .model-snapshot-grid {
                background: rgba(255, 255, 255, 0.12);
                display: grid;
                gap: 1px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .model-snapshot-item {
                background: #09090b;
                padding: 16px;
            }

            .model-snapshot-icon {
                align-items: center;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 999px;
                display: inline-flex;
                height: 40px;
                justify-content: center;
                margin-bottom: 10px;
                width: 40px;
            }

            .model-snapshot-icon img {
                display: block;
                filter: invert(1);
                height: 24px;
                object-fit: contain;
                opacity: 0.95;
                width: 24px;
            }

            .model-polaroid-grid {
                display: grid;
                gap: 8px;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .profile-credit-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fill, minmax(132px, 1fr));
            }

            .profile-credit-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                color: #111827;
                display: block;
                overflow: hidden;
                text-decoration: none;
                transition: border-color .18s ease, transform .18s ease;
            }

            .profile-credit-card:hover {
                border-color: #111827;
                transform: translateY(-2px);
            }

            .profile-credit-thumb {
                aspect-ratio: 1 / 1;
                background: #f3f4f6;
                overflow: hidden;
            }

            .profile-credit-thumb img {
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .profile-credit-body {
                padding: 10px 12px 12px;
            }

            .profile-credit-body strong {
                display: block;
                font-size: 13px;
                line-height: 1.3;
            }

            .profile-credit-body span {
                color: #6b7280;
                display: block;
                font-size: 11px;
                font-weight: 700;
                margin-top: 4px;
                text-transform: uppercase;
            }

            .profile-pending-credit {
                align-items: center;
                background: #fffaf0;
                border: 1px solid #fde68a;
                border-radius: 16px;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                padding: 14px;
            }

            .profile-pending-credit strong,
            .profile-pending-credit span {
                display: block;
            }

            .profile-pending-credit span {
                color: #92400e;
                font-size: 12px;
                margin-top: 2px;
            }

            .profile-credit-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .profile-credit-actions button {
                border-radius: 999px;
                font-size: 12px;
                font-weight: 800;
                padding: 8px 10px;
            }

            .profile-credit-actions button:first-child {
                background: #111827;
                border: 1px solid #111827;
                color: #fff;
            }

            .profile-credit-actions button:not(:first-child) {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #374151;
            }

            @media (max-width: 1023px) {
                .model-profile-layout {
                    grid-template-columns: 1fr;
                }

                .model-hero-content {
                    grid-template-columns: auto minmax(0, 1fr);
                }

                .model-hero-action-column {
                    align-items: flex-start;
                    grid-column: 1 / -1;
                    padding-top: 0;
                }

                .model-hero-socials,
                .model-hero-primary-actions {
                    justify-content: flex-start;
                }

                .model-profile-side {
                    position: static;
                }
            }

            @media (max-width: 640px) {
                .model-hero-content {
                    grid-template-columns: 1fr;
                }

                .model-hero-details {
                    padding-top: 0;
                }

                .connection-popover[open]::before {
                    background: rgba(15, 23, 42, .28);
                    content: "";
                    inset: 0;
                    position: fixed;
                    z-index: 79;
                }

                .connection-popover-panel {
                    bottom: 20px;
                    left: 16px;
                    margin-top: 0;
                    position: fixed;
                    right: 16px;
                    top: auto;
                    width: auto;
                    z-index: 80;
                }

                .model-profile-card {
                    padding: 20px;
                }

                .model-profile-card-header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .model-gallery-grid,
                .model-featured-grid,
                .profile-credit-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    @endpush

    @php
        $ownerCanManage = $ownerCanManage ?? (auth()->check() && auth()->id() === $user->id);
        $portfolioImages = collect($portfolioImages ?? []);
        $portfolioMediaGroups = collect($portfolioMediaGroups ?? []);
        $publicGalleries = collect($publicGalleries ?? []);
        $socialLinks = collect($profile->social_links ?? [])->filter(fn ($link) => !empty($link['url']));
        $platformMeta = [
            'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
            'facebook' => ['label' => 'Facebook', 'icon' => 'fab fa-facebook'],
            'x' => ['label' => 'X', 'icon' => 'fab fa-x-twitter'],
            'tiktok' => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
            'youtube' => ['label' => 'YouTube', 'icon' => 'fab fa-youtube'],
            'behance' => ['label' => 'Behance', 'icon' => 'fab fa-behance'],
            'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
            'website' => ['label' => 'Website', 'icon' => 'fas fa-globe'],
        ];
        $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties('model');
        $validSpecialties = $profile->specialties ? array_intersect_key(array_flip($profile->specialties), $specialtiesOptions) : [];
        $hairColors = \App\Helpers\ModelProfileOptions::hairColors();
        $eyeColors = \App\Helpers\ModelProfileOptions::eyeColors();
        $shoeSizeRegions = \App\Helpers\ModelProfileOptions::shoeSizeRegions();
        $dressSizeRegions = \App\Helpers\ModelProfileOptions::dressSizeRegions();
        $polaroidLabelOptions = [
            'front' => 'Front',
            'left_side' => 'Left Side',
            'right_side' => 'Right Side',
            'back' => 'Back',
            'full_body' => 'Full Body',
            'three_quarter' => 'Three Quarter',
            'close_up' => 'Close Up',
            'profile' => 'Profile',
        ];
        $iconBase = 'assets/graphics/model-icons/';
        $isMaleProfile = $profile->gender === 'male';
        $snapshotItems = collect([
            $profile->height ? ['label' => 'Height', 'value' => $profile->height, 'icon' => $iconBase . 'height-clean.png'] : null,
            $isMaleProfile && $profile->chest ? ['label' => 'Chest', 'value' => $profile->chest, 'icon' => $iconBase . 'm-chest-clean.png'] : null,
            !$isMaleProfile && $profile->bust ? ['label' => 'Bust', 'value' => $profile->bust, 'icon' => $iconBase . 'f-bust-clean.png'] : null,
            $profile->waist ? ['label' => 'Waist', 'value' => $profile->waist, 'icon' => $iconBase . ($isMaleProfile ? 'm-waist-clean.png' : 'f-waist-clean.png')] : null,
            !$isMaleProfile && $profile->hips ? ['label' => 'Hips', 'value' => $profile->hips, 'icon' => $iconBase . 'f-hips-clean.png'] : null,
            $isMaleProfile && $profile->inseam ? ['label' => 'Inseam', 'value' => $profile->inseam, 'icon' => $iconBase . 'm-inseam-clean.png'] : null,
            !$isMaleProfile && $profile->dress_size ? ['label' => 'Dress', 'value' => $profile->dress_size, 'icon' => $iconBase . 'f-dress-size-clean.png'] : null,
            $isMaleProfile && $profile->suit_size ? ['label' => 'Suit', 'value' => $profile->suit_size, 'icon' => $iconBase . 'm-suit-clean.png'] : null,
            $profile->shoe_size ? ['label' => 'Shoes', 'value' => $profile->shoe_size, 'icon' => $iconBase . ($isMaleProfile ? 'm-shoe-size-clean.png' : 'f-shoe-size-clean.png')] : null,
            $profile->hair_color ? ['label' => 'Hair', 'value' => $profile->hair_color, 'icon' => $iconBase . ($isMaleProfile ? 'm-hair-clean.png' : 'f-hair-clean.png')] : null,
            $profile->eye_color ? ['label' => 'Eyes', 'value' => $profile->eye_color, 'icon' => $iconBase . 'eye-colour-clean.png'] : null,
        ])->filter()->values();
    @endphp

    <div
        class="py-12"
        x-data="profileHeaderMedia({
            mediaGroups: @js($portfolioMediaGroups->map(fn ($group) => [
                'id' => $group['id'],
                'label' => $group['label'],
                'count' => $group['count'],
                'cover' => $group['cover'] ? asset($group['cover']) : '',
                'images' => collect($group['images'])->map(fn ($image) => [
                    'id' => $image->id,
                    'preview' => asset($image->thumbnail_path ?? $image->full_path),
                    'full' => asset($image->full_path ?? $image->thumbnail_path),
                ])->values(),
            ])->values()),
            profilePreview: @js($profile->profile_photo_path ? asset($profile->profile_photo_path) : ''),
            coverPreview: @js($profile->cover_photo_path ? asset($profile->cover_photo_path) : ''),
        })"
        @keydown.escape.window="close()"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="bg-white shadow sm:rounded-lg mb-6 overflow-visible">
                <div class="bg-gray-100 h-48 md:h-64 relative overflow-hidden sm:rounded-t-lg">
                    @if($profile->cover_photo_path)
                        <img :src="coverPreview || @js(asset($profile->cover_photo_path))" alt="Cover" class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                            <i class="fas fa-panorama text-3xl"></i>
                        </div>
                    @endif
                    @if($ownerCanManage)
                        <button
                            type="button"
                            @click="open('cover')"
                            class="group absolute inset-0 flex items-center justify-center bg-black/0 text-white transition hover:bg-black/20 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-black"
                            aria-label="Manage cover photo"
                        >
                            <span class="inline-flex items-center gap-2 rounded-full bg-black/75 px-3 py-2 text-sm font-semibold opacity-0 shadow-lg transition group-hover:opacity-100 hover:opacity-100 focus:opacity-100">
                                <i class="fas fa-image text-xs"></i>
                                <span>Manage cover</span>
                            </span>
                        </button>
                    @endif
                </div>
                <div class="p-6 md:p-8">
                    <div class="model-hero-content">
                        <div class="relative -mt-16 md:-mt-20">
                            @if($profile->profile_photo_path)
                                @if($ownerCanManage)
                                    <button type="button" @click="open('profile')" class="group relative block rounded-full focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2" aria-label="Manage profile photo">
                                        <img :src="profilePreview || @js(asset($profile->profile_photo_path))" alt="{{ $profile->display_name }}" class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white object-cover shadow-lg">
                                        <span class="absolute inset-0 flex items-center justify-center rounded-full bg-black/0 text-white transition group-hover:bg-black/30">
                                            <span class="rounded-full bg-black/75 px-3 py-2 text-xs font-semibold opacity-0 shadow transition group-hover:opacity-100">Manage</span>
                                        </span>
                                    </button>
                                @else
                                    <img :src="profilePreview || @js(asset($profile->profile_photo_path))" alt="{{ $profile->display_name }}" class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white object-cover shadow-lg">
                                @endif
                            @else
                                @if($ownerCanManage)
                                    <button type="button" @click="open('profile')" class="group w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white bg-gray-300 flex items-center justify-center shadow-lg transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2" aria-label="Add profile photo">
                                        <span class="text-center text-gray-600 transition group-hover:text-gray-800">
                                            <i class="fas fa-user text-3xl block"></i>
                                            <span class="mt-1 block text-xs font-semibold">Add photo</span>
                                        </span>
                                    </button>
                                @else
                                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white bg-gray-300 flex items-center justify-center shadow-lg">
                                        <span class="text-4xl text-gray-600">{{ substr($profile->display_name, 0, 1) }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="model-hero-details">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h1 class="text-3xl font-bold text-black">{{ $profile->display_name }}</h1>
                                @if($profile->isVerified())
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-green-500 text-white shadow-sm" title="Verified profile" aria-label="Verified profile">
                                        <i class="fas fa-check text-xs"></i>
                                    </span>
                                @endif
                            </div>
                            <p class="mb-3 text-sm font-semibold text-gray-500">{{ '@' . $user->username }}</p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600">
                                @if($profile->location_city || $profile->location_country)
                                    <span>
                                        <i class="fas fa-map-marker-alt mr-1.5"></i>
                                        {{ $profile->location_city }}{{ $profile->location_city && $profile->location_country ? ', ' : '' }}{{ $profile->location_country }}
                                    </span>
                                @endif
                                @if($profile->age)
                                    <span>
                                        <i class="fas fa-cake-candles mr-1.5"></i>
                                        {{ $profile->age }} years old
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="model-hero-action-column">
                            <div class="model-hero-socials">
                                @if($socialLinks->isNotEmpty())
                                    @foreach($socialLinks as $link)
                                        @php
                                            $meta = $platformMeta[$link['platform']] ?? [
                                                'label' => ucfirst($link['platform'] ?? 'Link'),
                                                'icon' => 'fas fa-link',
                                            ];
                                        @endphp
                                        <a
                                            href="{{ $link['url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 transition hover:border-black hover:text-black"
                                            aria-label="{{ $meta['label'] }}"
                                            title="{{ $meta['label'] }}"
                                        >
                                            <i class="{{ $meta['icon'] }}"></i>
                                        </a>
                                    @endforeach
                                @endif
                            </div>

                            <div class="model-hero-primary-actions">
                                @if($ownerCanManage)
                                    <a href="{{ route('profile.model.edit') }}" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 transition hover:border-black">
                                        <i class="fas fa-pen mr-2"></i>Edit
                                    </a>
                                @elseif(auth()->check() && auth()->id() !== $user->id)
                                    <a href="{{ route('messages.create', ['user_id' => $user->id]) }}" class="inline-flex items-center rounded-full bg-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">
                                        <i class="fas fa-envelope mr-2"></i>Message
                                    </a>
                                    @if(!$viewerConnection)
                                        <details class="connection-popover">
                                            <summary class="inline-flex cursor-pointer list-none items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:border-black">
                                                <i class="fas fa-user-plus mr-2"></i>Connect
                                            </summary>
                                            <form method="POST" action="{{ route('connections.store', $user) }}" class="connection-popover-panel">
                                                @csrf
                                                <label for="connection-message-model" class="text-sm font-bold text-gray-900">Add a note</label>
                                                <textarea id="connection-message-model" name="message" maxlength="125" class="mt-2 block min-h-20 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-black focus:ring-black" placeholder="Optional, up to 125 characters"></textarea>
                                                <div class="mt-3 flex justify-end">
                                                    <button type="submit" class="rounded-full bg-black px-4 py-2 text-xs font-bold text-white">Send request</button>
                                                </div>
                                            </form>
                                        </details>
                                    @elseif($viewerConnection->status === \App\Models\Connection::STATUS_PENDING)
                                        @if((int) $viewerConnection->requester_id === (int) auth()->id())
                                            <form method="POST" action="{{ route('connections.destroy', $viewerConnection) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-red-300 hover:text-red-700">
                                                    <i class="fas fa-xmark mr-2"></i>Cancel request
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-600">Connection requested</span>
                                        @endif
                                    @elseif($viewerConnection->status === \App\Models\Connection::STATUS_ACCEPTED)
                                        <span class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700">Connected</span>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">
                                        <i class="fas fa-envelope mr-2"></i>Log in to message
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="model-profile-layout">
                <main class="model-profile-main">
                    @if($profile->bio || $ownerCanManage)
                        <section class="model-profile-card profile-inline-section" data-profile-bio-section>
                            <div class="profile-inline-actions">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                        <i class="fas fa-quote-left"></i>
                                    </span>
                                    <h2 class="text-xl font-semibold text-black">Bio</h2>
                                </div>
                                @if($ownerCanManage)
                                    <button type="button" class="profile-icon-action" data-profile-bio-edit aria-label="Edit bio">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="profile-bio-display {{ $profile->bio ? '' : 'profile-bio-empty' }}" data-profile-bio-display>{{ $profile->bio ?: 'Add a short bio so visitors understand your style, experience, and personality.' }}</div>
                            @if($ownerCanManage)
                                <form class="profile-inline-editor" data-profile-bio-form action="{{ route('profile.model.bio.update') }}">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="bio" maxlength="2000" data-profile-bio-input>{{ $profile->bio }}</textarea>
                                    <p class="profile-inline-help">Plain text only. Paragraphs and line breaks are kept; links, HTML, and embeds are stripped out.</p>
                                    <div class="profile-inline-buttons">
                                        <span class="profile-inline-status" data-profile-bio-status></span>
                                        <button type="button" class="profile-inline-cancel" data-profile-bio-cancel>Cancel</button>
                                        <button type="submit" class="profile-inline-save">Save Bio</button>
                                    </div>
                                </form>
                            @endif
                        </section>
                    @endif

                    <section class="model-profile-card">
                        <div class="model-profile-card-header">
                            <div>
                                <p class="photographer-kicker">Network</p>
                                <h2 class="text-2xl font-semibold text-black">Connections</h2>
                            </div>
                            <i class="fas fa-user-group text-gray-300"></i>
                        </div>
                        @if(($connections ?? collect())->isNotEmpty())
                            <div class="space-y-5">
                                @foreach($connections as $roleLabel => $roleConnections)
                                    <div>
                                        <h3 class="mb-3 text-sm font-bold text-gray-900">{{ $roleLabel }}</h3>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            @foreach($roleConnections as $connectedUser)
                                                @php
                                                    $connectedProfile = $connectedUser->is_photographer ? $connectedUser->photographerProfile : $connectedUser->modelProfile;
                                                    $connectedName = $connectedProfile?->display_name ?: $connectedUser->display_name ?: $connectedUser->name;
                                                    $connectedPhoto = $connectedProfile?->profile_photo_path;
                                                    $connectedRoute = $connectedUser->is_photographer
                                                        ? route('photographers.show', $connectedUser->profileRouteIdentifier())
                                                        : route('models.show', $connectedUser->profileRouteIdentifier());
                                                @endphp
                                                <a href="{{ $connectedRoute }}" class="flex items-center gap-3 rounded-2xl border border-gray-200 p-3 transition hover:border-black">
                                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100 font-bold text-gray-600">
                                                        @if($connectedPhoto)
                                                            <img src="{{ asset($connectedPhoto) }}" alt="" class="h-full w-full object-cover">
                                                        @else
                                                            {{ mb_substr($connectedName, 0, 1) }}
                                                        @endif
                                                    </span>
                                                    <span>
                                                        <strong class="block text-sm text-gray-900">{{ $connectedName }}</strong>
                                                        <small class="font-semibold text-gray-500">{{ '@' . $connectedUser->username }}</small>
                                                    </span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No public connections yet.</p>
                        @endif
                    </section>

                    <section class="model-profile-card">
                        <div class="model-profile-card-header">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Portfolio</p>
                                <h2 class="mt-1 text-2xl font-semibold text-black">Galleries</h2>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if($ownerCanManage)
                                    <a href="{{ route('portfolio.galleries.index') }}" class="profile-text-action">
                                        <i class="fas fa-images"></i>
                                        <span>Manage Galleries</span>
                                    </a>
                                @endif
                                @if($publicGalleries->isNotEmpty())
                                    <a href="{{ route('models.galleries', $user->profileRouteIdentifier()) }}" class="model-view-all-link">
                                        <span>View All</span>
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if($publicGalleries->isNotEmpty())
                            <div class="model-gallery-grid">
                                @foreach($publicGalleries->take(4) as $gallery)
                                    @php
                                        $galleryCover = $gallery->cover_image_path
                                            ?? $gallery->coverImage?->thumbnail_path
                                            ?? $gallery->images->first()?->thumbnail_path;
                                    @endphp
                                    <a href="{{ route('public.galleries.show', $gallery->id) }}" class="model-gallery-card">
                                        <div class="model-gallery-thumb">
                                            @if($galleryCover)
                                                <img src="{{ asset($galleryCover) }}" alt="{{ $gallery->name }}">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-gray-400">
                                                    <i class="fas fa-images text-3xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="model-gallery-body">
                                            <h3 class="text-base font-semibold text-gray-900">{{ $gallery->name }}</h3>
                                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $gallery->images_count }} {{ $gallery->images_count === 1 ? 'image' : 'images' }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-300 px-5 py-8 text-center">
                                <i class="fas fa-images text-3xl text-gray-300"></i>
                                <p class="mt-3 text-sm font-semibold text-gray-700">No public galleries yet.</p>
                            </div>
                        @endif
                    </section>

                    @if(($featuredAlbumCredits ?? collect())->isNotEmpty() || ($featuredImageCredits ?? collect())->isNotEmpty())
                        <article class="photographer-card">
                            <div class="photographer-card-header">
                                <div>
                                    <p class="photographer-kicker">Collaborations</p>
                                    <h2 class="photographer-heading">Featured In</h2>
                                </div>
                                <i class="fas fa-user-tag photographer-muted-icon"></i>
                            </div>

                            @if(($featuredAlbumCredits ?? collect())->isNotEmpty())
                                <div style="margin-bottom: 24px;">
                                    <p class="photographer-kicker" style="margin-bottom: 12px;">Galleries</p>
                                    <div class="photographer-tagged-grid">
                                        @foreach($featuredAlbumCredits->take(4) as $credit)
                                            @php
                                                $album = $credit->creditable;
                                                $albumCover = $album->cover_image_path ?? $album->coverImage?->thumbnail_path;
                                            @endphp
                                            <a href="{{ route('public.galleries.show', $album->id) }}" class="photographer-image-card">
                                                <div class="photographer-square">
                                                    @if($albumCover)
                                                        <img src="{{ asset($albumCover) }}" alt="{{ $album->name }}">
                                                    @else
                                                        <div class="photographer-empty"><i class="fas fa-images"></i></div>
                                                    @endif
                                                </div>
                                                <div class="profile-credit-body">
                                                    <strong>{{ $album->name }}</strong>
                                                    <span>{{ $credit->owner?->display_name ?? $credit->owner?->name }}</span>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(($featuredImageCredits ?? collect())->isNotEmpty())
                                <div>
                                    <p class="photographer-kicker" style="margin-bottom: 12px;">Photos</p>
                                    <div class="photographer-tagged-grid">
                                        @foreach($featuredImageCredits->take(6) as $credit)
                                            @php
                                                $image = $credit->creditable;
                                                $imageUrl = asset($image->thumbnail_path ?? $image->full_path);
                                                $fullUrl = asset($image->full_path ?? $image->thumbnail_path);
                                            @endphp
                                            <button type="button" class="photographer-image-card photographer-square" onclick="openLightbox('{{ $fullUrl }}')">
                                                <img src="{{ $imageUrl }}" alt="Credited portfolio image">
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </article>
                    @endif

                    <section class="model-profile-card model-profile-card-dashed">
                        <div class="flex items-start gap-4">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                <i class="fas fa-stream"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Coming Next</p>
                                <h2 class="mt-1 text-xl font-semibold text-black">Feed</h2>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">This area will become the member feed: profile posts, updates, and tagged posts from collaborators.</p>
                            </div>
                        </div>
                    </section>

                    @if($featuredImages->count() > 0)
                        <section class="model-profile-card">
                            <div class="model-profile-card-header">
                                <h2 class="text-2xl font-semibold text-black">Featured Work</h2>
                                <i class="fas fa-star text-gray-300"></i>
                            </div>
                            <div class="model-featured-grid">
                                @foreach($featuredImages as $image)
                                    <div class="aspect-square overflow-hidden rounded-lg">
                                        <img src="{{ asset($image->thumbnail_path) }}" alt="{{ $image->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer" onclick="openLightbox('{{ asset($image->full_path) }}')">
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </main>

                <aside class="model-profile-side">
                    @if($snapshotItems->isNotEmpty() || $ownerCanManage)
                        <section class="model-snapshot-card">
                            <div class="model-snapshot-header">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/55">Model Snapshot</p>
                                    <h2 class="mt-1 text-xl font-bold">Measurements & Appearance</h2>
                                </div>
                                @if($ownerCanManage)
                                    <button type="button" data-open-quick-modal="model-measurements" class="model-snapshot-action">
                                        <i class="fas fa-pen"></i>
                                        <span>Edit</span>
                                    </button>
                                @endif
                            </div>
                            @if($snapshotItems->isNotEmpty())
                                <div class="model-snapshot-grid">
                                    @foreach($snapshotItems as $item)
                                        <div class="model-snapshot-item">
                                            <div class="model-snapshot-icon">
                                                <img src="{{ asset($item['icon']) }}" alt="" aria-hidden="true">
                                            </div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-white/45">{{ $item['label'] }}</p>
                                            <p class="mt-1 text-sm font-semibold text-white">{{ $item['value'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-sm leading-6 text-white/65">Add your measurements and appearance details so casting and creative teams can quickly understand your fit.</p>
                            @endif
                        </section>
                    @endif

                    @if($polaroids->count() > 0 || $ownerCanManage)
                        <section class="model-profile-card">
                            <div class="model-profile-card-header">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Reference</p>
                                    <h2 class="mt-1 text-xl font-semibold text-black">Polaroids</h2>
                                </div>
                                @if($ownerCanManage)
                                    <button type="button" @click="{{ $polaroids->count() > 0 ? '$dispatch(\'open-modal\', \'manage-polaroids\')' : 'openUploadModal({ mode: \'polaroids\' })' }}" class="profile-icon-action" aria-label="{{ $polaroids->count() > 0 ? 'Manage polaroids' : 'Upload polaroids' }}">
                                        <i class="fas {{ $polaroids->count() > 0 ? 'fa-sliders' : 'fa-plus' }}"></i>
                                    </button>
                                @else
                                    <i class="fas fa-camera-retro text-gray-300"></i>
                                @endif
                            </div>
                            @if($polaroids->count() > 0)
                                <div class="model-polaroid-grid">
                                    @foreach($polaroids->take(6) as $image)
                                        @php
                                            $polaroidLabel = collect($image->tags ?? [])->first();
                                            $publicPolaroidLabels = [
                                                'front' => 'Front',
                                                'left_side' => 'Left',
                                                'right_side' => 'Right',
                                                'back' => 'Back',
                                                'full_body' => 'Full',
                                                'three_quarter' => '3/4',
                                                'close_up' => 'Close',
                                                'profile' => 'Profile',
                                            ];
                                        @endphp
                                        <button type="button" class="relative aspect-square overflow-hidden rounded-lg bg-gray-100" onclick="openLightbox('{{ asset($image->full_path) }}')">
                                            <img src="{{ asset($image->thumbnail_path) }}" alt="Polaroid" class="h-full w-full object-cover transition hover:scale-105">
                                            @if($polaroidLabel && isset($publicPolaroidLabels[$polaroidLabel]))
                                                <span class="absolute left-1.5 top-1.5 rounded-full bg-black/75 px-2 py-0.5 text-[10px] font-semibold text-white">
                                                    {{ $publicPolaroidLabels[$polaroidLabel] }}
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <p class="profile-section-empty">Upload your core polaroids so they are easy to find from the public profile.</p>
                            @endif
                        </section>
                    @endif

                    @if(!empty($validSpecialties) || $ownerCanManage)
                        <section class="model-profile-card">
                            <div class="model-profile-card-header">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Profile Fit</p>
                                    <h2 class="mt-1 text-xl font-semibold text-black">Specialties</h2>
                                </div>
                                @if($ownerCanManage)
                                    <button type="button" data-open-quick-modal="model-specialties" class="profile-icon-action" aria-label="Edit specialties">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                @else
                                    <i class="fas fa-bullseye text-gray-300"></i>
                                @endif
                            </div>
                            @if(!empty($validSpecialties))
                                <div class="profile-chip-list">
                                    @foreach(array_keys($validSpecialties) as $specialty)
                                        <span class="profile-chip">{{ $specialtiesOptions[$specialty] }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="profile-section-empty">Add specialties so visitors can quickly understand the kind of modelling work you are suited for.</p>
                            @endif
                        </section>
                    @endif
                </aside>
            </div>
        </div>

        @if($ownerCanManage)
            <div class="quick-edit-overlay" data-quick-modal="model-measurements">
                <form class="quick-edit-modal" action="{{ route('profile.model.measurements.update') }}" data-quick-edit-form>
                    @csrf
                    @method('PATCH')
                    <div class="quick-edit-header">
                        <div>
                            <h2 class="text-xl font-bold text-gray-950">Edit Measurements</h2>
                            <p class="mt-1 text-sm text-gray-600">Quick update the details shown in your public profile snapshot.</p>
                        </div>
                        <button type="button" data-close-quick-modal class="profile-icon-action" aria-label="Close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="quick-edit-body">
                        <div class="quick-edit-grid">
                            <div class="quick-edit-field">
                                <label>Height (cm)</label>
                                <input type="number" name="height_cm" min="100" max="250" value="{{ $profile->height_cm }}">
                            </div>
                            <div class="quick-edit-field">
                                <label>{{ $profile->gender === 'male' ? 'Chest' : 'Bust' }} (cm)</label>
                                <input type="number" step="0.1" name="{{ $profile->gender === 'male' ? 'chest_cm' : 'bust_cm' }}" min="20" max="200" value="{{ $profile->gender === 'male' ? $profile->chest_cm : $profile->bust_cm }}">
                            </div>
                            <div class="quick-edit-field">
                                <label>Waist (cm)</label>
                                <input type="number" step="0.1" name="waist_cm" min="20" max="200" value="{{ $profile->waist_cm }}">
                            </div>
                            @if($profile->gender === 'male')
                                <div class="quick-edit-field">
                                    <label>Inseam (cm)</label>
                                    <input type="number" step="0.1" name="inseam_cm" min="20" max="150" value="{{ $profile->inseam_cm }}">
                                </div>
                            @else
                                <div class="quick-edit-field">
                                    <label>Hips (cm)</label>
                                    <input type="number" step="0.1" name="hips_cm" min="20" max="200" value="{{ $profile->hips_cm }}">
                                </div>
                            @endif
                            <div class="quick-edit-field">
                                <label>Shoe Region</label>
                                <select name="shoe_size_region">
                                    <option value="">Select region</option>
                                    @foreach($shoeSizeRegions as $key => $label)
                                        <option value="{{ $key }}" @selected($profile->shoe_size_region === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="quick-edit-field">
                                <label>Shoe Size</label>
                                <input type="text" name="shoe_size_value" value="{{ $profile->shoe_size_value }}">
                            </div>
                            @if($profile->gender !== 'male')
                                <div class="quick-edit-field">
                                    <label>Dress Region</label>
                                    <select name="dress_size_region">
                                        <option value="">Select region</option>
                                        @foreach($dressSizeRegions as $key => $label)
                                            <option value="{{ $key }}" @selected($profile->dress_size_region === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="quick-edit-field">
                                    <label>Dress Size</label>
                                    <input type="text" name="dress_size_value" value="{{ $profile->dress_size_value }}">
                                </div>
                            @endif
                            <div class="quick-edit-field">
                                <label>Hair Colour</label>
                                <select name="hair_color">
                                    <option value="">Select hair colour</option>
                                    @foreach($hairColors as $key => $label)
                                        <option value="{{ $label }}" @selected($profile->hair_color === $label || $profile->hair_color === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="quick-edit-field">
                                <label>Eye Colour</label>
                                <select name="eye_color">
                                    <option value="">Select eye colour</option>
                                    @foreach($eyeColors as $key => $label)
                                        <option value="{{ $label }}" @selected($profile->eye_color === $label || $profile->eye_color === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="quick-edit-footer">
                        <span class="quick-edit-message" data-quick-edit-message></span>
                        <button type="button" data-close-quick-modal class="profile-inline-cancel">Cancel</button>
                        <button type="submit" class="profile-inline-save">Save</button>
                    </div>
                </form>
            </div>

            <div class="quick-edit-overlay" data-quick-modal="model-specialties">
                <form class="quick-edit-modal" action="{{ route('profile.model.specialties.update') }}" data-quick-edit-form>
                    @csrf
                    @method('PATCH')
                    <div class="quick-edit-header">
                        <div>
                            <h2 class="text-xl font-bold text-gray-950">Edit Specialties</h2>
                            <p class="mt-1 text-sm text-gray-600">Choose the public specialties shown on your profile.</p>
                        </div>
                        <button type="button" data-close-quick-modal class="profile-icon-action" aria-label="Close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="quick-edit-body">
                        <p class="quick-edit-checklist-label">Specialties</p>
                        <div class="quick-edit-checklist">
                            @foreach($specialtiesOptions as $key => $label)
                                <label class="quick-edit-check">
                                    <input type="checkbox" name="specialties[]" value="{{ $key }}" @checked(in_array($key, $profile->specialties ?? [], true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="quick-edit-footer">
                        <span class="quick-edit-message" data-quick-edit-message></span>
                        <button type="button" data-close-quick-modal class="profile-inline-cancel">Cancel</button>
                        <button type="submit" class="profile-inline-save">Save</button>
                    </div>
                </form>
            </div>

            <x-polaroid-manager-modal :polaroids="$polaroids" :polaroid-label-options="$polaroidLabelOptions" />

            <div
                x-show="isOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-[90] flex items-center justify-center bg-gray-950/75 px-4 py-6 backdrop-blur-sm"
                @click.self="close()"
            >
                <form method="POST" action="{{ route('profile.model.media.update') }}" enctype="multipart/form-data" class="flex max-h-[88vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/10" @submit.prevent="saveMedia($event)">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="profile_photo_image_id" :value="activeSlot === 'profile' && mode === 'portfolio' ? selectedImageId : ''">
                    <input type="hidden" name="cover_photo_image_id" :value="activeSlot === 'cover' && mode === 'portfolio' ? selectedImageId : ''">
                    <input type="hidden" name="profile_photo_crop_data" :value="profileCropData">
                    <input type="hidden" name="cover_photo_crop_data" :value="coverCropData">

                    <div class="flex items-start justify-between gap-5 border-b border-gray-200 bg-white px-6 py-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="activeSlot === 'cover' ? 'Cover Photo' : 'Profile Photo'"></h3>
                            <p class="mt-1 text-sm text-gray-500">Upload a new image or choose one from your portfolio.</p>
                        </div>
                        <button type="button" @click="close()" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-900" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="min-h-0 overflow-y-auto bg-gray-50 p-5">
                        <div x-show="mediaError" x-cloak class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" x-text="mediaError"></div>
                        <div x-show="mediaStatus" x-cloak class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700" x-text="mediaStatus"></div>
                        <div class="space-y-4">
                            <div x-show="activeSlot === 'profile' && profileStep === 'start'" x-cloak class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                                <div class="flex flex-col items-center gap-4 text-center">
                                    <div class="profile-media-current-thumb bg-gray-100 ring-1 ring-gray-200">
                                        <template x-if="profilePreview">
                                            <img :src="profilePreview" alt="">
                                        </template>
                                        <template x-if="!profilePreview">
                                            <div class="profile-media-current-placeholder text-gray-400">
                                                <i class="fas fa-user text-2xl"></i>
                                            </div>
                                        </template>
                                    </div>
                                    <div>
                                        <p class="text-base font-semibold text-gray-900" x-text="profilePreview ? 'Update profile photo' : 'Add profile photo'"></p>
                                        <p class="mt-1 text-sm text-gray-500">Upload a new image or choose one from your portfolio.</p>
                                    </div>
                                    <div class="flex flex-wrap justify-center gap-3">
                                        <button type="button" @click="showUploadStep()" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900">
                                            <i class="fas fa-upload mr-2 text-xs"></i>Upload photo
                                        </button>
                                        <button type="button" @click="showPortfolioGroups()" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50">
                                            <i class="fas fa-images mr-2 text-xs"></i>Choose from portfolio
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div x-show="activeSlot === 'profile' && profileStep === 'upload'" x-cloak class="rounded-xl border border-gray-200 bg-white p-5">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <button type="button" @click="goBackProfileStep()" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        <i class="fas fa-arrow-left text-xs"></i>Back
                                    </button>
                                </div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Upload profile photo</label>
                                <input
                                    type="file"
                                    name="profile_photo_upload"
                                    x-ref="profileUpload"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border file:border-gray-300 file:bg-white file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-800 hover:file:bg-gray-50"
                                    @change="handleUpload"
                                >
                            </div>

                            <div x-show="activeSlot === 'profile' && profileStep === 'crop'" x-cloak class="profile-media-crop-card rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Crop your new profile photo</p>
                                        <p class="text-xs text-gray-500">Move the image, resize the crop box, or use your mouse wheel / pinch to zoom.</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" @click="goBackProfileStep()" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                            <i class="fas fa-arrow-left mr-2 text-xs"></i>Back
                                        </button>
                                        <button x-show="preview" x-cloak type="button" @click="resetCropper()" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                            <i class="fas fa-rotate-left mr-2 text-xs"></i>Reset
                                        </button>
                                    </div>
                                </div>
                                <div class="profile-photo-cropper rounded-lg bg-gray-100 ring-1 ring-gray-200">
                                    <template x-if="preview">
                                        <img
                                            x-ref="profileCropImage"
                                            :src="preview"
                                            alt=""
                                            class="object-contain"
                                            @load="initProfileCropper()"
                                        >
                                    </template>
                                    <template x-if="!preview">
                                        <div class="flex h-full flex-col items-center justify-center text-gray-400">
                                            <i class="fas fa-user text-4xl"></i>
                                            <p class="mt-3 text-sm font-semibold text-gray-500">Choose or upload an image to start cropping.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div x-show="activeSlot === 'cover' && coverStep === 'start'" x-cloak class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                                <div class="flex flex-col items-center gap-4 text-center">
                                    <div class="h-24 w-full max-w-md overflow-hidden rounded-lg bg-gray-100 ring-1 ring-gray-200">
                                        <template x-if="coverPreview">
                                            <img :src="coverPreview" alt="" class="h-full w-full object-cover">
                                        </template>
                                        <template x-if="!coverPreview">
                                            <div class="flex h-full w-full items-center justify-center text-gray-400">
                                                <i class="fas fa-panorama text-2xl"></i>
                                            </div>
                                        </template>
                                    </div>
                                    <div>
                                        <p class="text-base font-semibold text-gray-900" x-text="coverPreview ? 'Update cover photo' : 'Add cover photo'"></p>
                                        <p class="mt-1 text-sm text-gray-500">Upload a wide image or choose one from your portfolio.</p>
                                    </div>
                                    <div class="flex flex-wrap justify-center gap-3">
                                        <button type="button" @click="showUploadStep()" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900">
                                            <i class="fas fa-upload mr-2 text-xs"></i>Upload cover
                                        </button>
                                        <button type="button" @click="showPortfolioGroups()" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50">
                                            <i class="fas fa-images mr-2 text-xs"></i>Choose from portfolio
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div x-show="activeSlot === 'cover' && coverStep === 'upload'" x-cloak class="rounded-xl border border-gray-200 bg-white p-5">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <button type="button" @click="goBackCoverStep()" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        <i class="fas fa-arrow-left text-xs"></i>Back
                                    </button>
                                </div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Upload cover photo</label>
                                <input
                                    type="file"
                                    name="cover_photo_upload"
                                    x-ref="coverUpload"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border file:border-gray-300 file:bg-white file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-800 hover:file:bg-gray-50"
                                    @change="handleUpload"
                                >
                            </div>

                            <div x-show="activeSlot === 'cover' && coverStep === 'crop'" x-cloak class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Crop your new cover photo</p>
                                        <p class="text-xs text-gray-500">Use a wide crop. Keep important details near the centre.</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" @click="goBackCoverStep()" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                            <i class="fas fa-arrow-left mr-2 text-xs"></i>Back
                                        </button>
                                        <button x-show="preview" x-cloak type="button" @click="resetCropper()" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                            <i class="fas fa-rotate-left mr-2 text-xs"></i>Reset
                                        </button>
                                    </div>
                                </div>
                                <div class="cover-photo-cropper rounded-lg bg-gray-100 ring-1 ring-gray-200">
                                    <template x-if="preview">
                                        <img
                                            x-ref="coverCropImage"
                                            :src="preview"
                                            alt=""
                                            class="object-contain"
                                            @load="initCoverCropper()"
                                        >
                                    </template>
                                    <template x-if="!preview">
                                        <div class="flex h-full flex-col items-center justify-center text-gray-400">
                                            <i class="fas fa-panorama text-4xl"></i>
                                            <p class="mt-3 text-sm font-semibold text-gray-500">Choose or upload an image to start cropping.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div x-show="(activeSlot === 'profile' && profileStep === 'portfolio-groups') || (activeSlot === 'cover' && coverStep === 'portfolio-groups')" x-cloak class="rounded-xl border border-gray-200 bg-white p-4">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <button type="button" @click="activeSlot === 'cover' ? goBackCoverStep() : goBackProfileStep()" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        <i class="fas fa-arrow-left text-xs"></i>Back
                                    </button>
                                    <p class="text-sm font-semibold text-gray-900">Choose an album</p>
                                </div>
                                @if($portfolioMediaGroups->isNotEmpty())
                                    <div class="grid max-h-[420px] grid-cols-2 gap-3 overflow-y-auto pr-1 lg:grid-cols-4">
                                        <template x-for="group in mediaGroups" :key="group.id">
                                            <button type="button" @click="openGroup(group)" class="overflow-hidden rounded-lg border border-gray-200 bg-white text-left shadow-sm transition hover:border-gray-400 hover:shadow-md">
                                                <div class="aspect-[5/3] bg-gray-100">
                                                    <template x-if="group.cover">
                                                        <img :src="group.cover" alt="" class="h-full w-full object-cover">
                                                    </template>
                                                    <template x-if="!group.cover">
                                                        <div class="flex h-full w-full items-center justify-center text-gray-400">
                                                            <i class="fas fa-images text-2xl"></i>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="px-3 py-2">
                                                    <p class="truncate text-sm font-semibold text-gray-900" x-text="group.label"></p>
                                                    <p class="text-xs text-gray-500" x-text="`${group.count} image${group.count === 1 ? '' : 's'}`"></p>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
                                        No portfolio images available yet.
                                    </div>
                                @endif
                            </div>

                            <div x-show="(activeSlot === 'profile' && profileStep === 'portfolio-images') || (activeSlot === 'cover' && coverStep === 'portfolio-images')" x-cloak class="rounded-xl border border-gray-200 bg-white p-4">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <button type="button" @click="backToGroups()" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        <i class="fas fa-arrow-left text-xs"></i>Albums
                                    </button>
                                    <p class="truncate text-sm font-semibold text-gray-900" x-text="activeGroup ? activeGroup.label : ''"></p>
                                </div>
                                <div class="grid max-h-[420px] grid-cols-4 gap-2 overflow-y-auto pr-1 sm:grid-cols-5 lg:grid-cols-6">
                                    <template x-for="image in activeGroupImages()" :key="image.id">
                                        <button type="button" @click="choosePortfolioImage(image)" class="aspect-square overflow-hidden rounded-md border-2 bg-white transition" :class="String(selectedImageId) === String(image.id) ? 'border-black ring-2 ring-black/10' : 'border-gray-200 hover:border-gray-400'">
                                            <img :src="image.preview" alt="" class="h-full w-full object-cover">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 flex items-center justify-end gap-3 border-t border-gray-200 bg-white px-6 py-4">
                        <button type="button" @click="close()" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" :disabled="isSaving">Cancel</button>
                        <button type="submit" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-60" :disabled="isSaving || (activeSlot === 'profile' && profileStep !== 'crop') || (activeSlot === 'cover' && coverStep !== 'crop')">
                            <span x-show="!isSaving">Save</span>
                            <span x-show="isSaving" x-cloak>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center" onclick="closeLightbox()">
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300">&times;</button>
    </div>

    <script>
        function profileHeaderMedia(config) {
            return {
                isOpen: false,
                activeSlot: 'profile',
                mode: 'upload',
                selectedImageId: '',
                mediaGroups: config.mediaGroups || [],
                portfolioStep: 'groups',
                profileStep: 'start',
                previousProfileStep: 'start',
                coverStep: 'start',
                previousCoverStep: 'start',
                activeGroup: null,
                profilePreview: config.profilePreview || '',
                coverPreview: config.coverPreview || '',
                preview: '',
                cropper: null,
                cropperInitTimer: null,
                originalFileName: 'profile-photo.jpg',
                profileCropData: '',
                coverCropData: '',
                cropSourceReady: false,
                isSaving: false,
                mediaError: '',
                mediaStatus: '',

                open(slot) {
                    this.activeSlot = slot;
                    this.mode = 'upload';
                    this.profileStep = slot === 'profile' ? 'start' : 'upload';
                    this.previousProfileStep = 'start';
                    this.coverStep = slot === 'cover' ? 'start' : 'upload';
                    this.previousCoverStep = 'start';
                    this.selectedImageId = '';
                    this.portfolioStep = 'groups';
                    this.activeGroup = null;
                    this.clearUploadInputs();
                    this.preview = slot === 'cover' ? this.coverPreview : this.profilePreview;
                    this.profileCropData = '';
                    this.coverCropData = '';
                    this.cropSourceReady = false;
                    this.mediaError = '';
                    this.mediaStatus = '';
                    this.isOpen = true;
                },

                close() {
                    this.destroyCropper();
                    this.isOpen = false;
                },

                setMode(mode) {
                    this.mode = mode;
                    this.selectedImageId = '';
                    this.portfolioStep = 'groups';
                    this.activeGroup = null;
                    this.clearUploadInputs();
                    this.preview = mode === 'upload' ? '' : (this.activeSlot === 'cover' ? this.coverPreview : this.profilePreview);
                    this.profileCropData = '';
                    this.coverCropData = '';
                    this.cropSourceReady = false;
                    this.destroyCropper();
                },

                openGroup(group) {
                    this.activeGroup = group;
                    this.portfolioStep = 'images';
                    if (this.activeSlot === 'profile') {
                        this.profileStep = 'portfolio-images';
                    } else {
                        this.coverStep = 'portfolio-images';
                    }
                },

                backToGroups() {
                    this.portfolioStep = 'groups';
                    this.activeGroup = null;
                    if (this.activeSlot === 'profile') {
                        this.profileStep = 'portfolio-groups';
                    } else {
                        this.coverStep = 'portfolio-groups';
                    }
                },

                activeGroupImages() {
                    return this.activeGroup ? this.activeGroup.images || [] : [];
                },

                choosePortfolioImage(image) {
                    this.mode = 'portfolio';
                    this.selectedImageId = image.id;
                    this.preview = image.full || image.preview;
                    this.originalFileName = `portfolio-${image.id}.jpg`;
                    this.profileCropData = '';
                    this.coverCropData = '';
                    this.cropSourceReady = true;
                    this.previousProfileStep = this.activeSlot === 'profile' ? 'portfolio-images' : this.previousProfileStep;
                    this.previousCoverStep = this.activeSlot === 'cover' ? 'portfolio-images' : this.previousCoverStep;
                    this.profileStep = this.activeSlot === 'profile' ? 'crop' : this.profileStep;
                    this.coverStep = this.activeSlot === 'cover' ? 'crop' : this.coverStep;
                    this.destroyCropper();
                    this.clearUploadInputs();
                    this.$nextTick(() => this.activeSlot === 'cover' ? this.initCoverCropper() : this.initProfileCropper());
                },

                handleUpload(event) {
                    const file = event.target.files ? event.target.files[0] : null;
                    if (!file) {
                        return;
                    }

                    this.mode = 'upload';
                    this.selectedImageId = '';
                    this.originalFileName = file.name || 'profile-photo.jpg';
                    this.profileCropData = '';
                    this.coverCropData = '';
                    this.cropSourceReady = true;
                    this.previousProfileStep = this.activeSlot === 'profile' ? 'upload' : this.previousProfileStep;
                    this.previousCoverStep = this.activeSlot === 'cover' ? 'upload' : this.previousCoverStep;
                    this.profileStep = this.activeSlot === 'profile' ? 'crop' : this.profileStep;
                    this.coverStep = this.activeSlot === 'cover' ? 'crop' : this.coverStep;
                    this.destroyCropper();

                    const reader = new FileReader();
                    reader.onload = (loadEvent) => {
                        this.preview = loadEvent.target.result;
                        this.$nextTick(() => this.activeSlot === 'cover' ? this.initCoverCropper() : this.initProfileCropper());
                    };
                    reader.readAsDataURL(file);
                },

                showUploadStep() {
                    this.mode = 'upload';
                    if (this.activeSlot === 'cover') {
                        this.coverStep = 'upload';
                        this.previousCoverStep = 'start';
                    } else {
                        this.profileStep = 'upload';
                        this.previousProfileStep = 'start';
                    }
                    this.selectedImageId = '';
                    this.profileCropData = '';
                    this.coverCropData = '';
                    this.cropSourceReady = false;
                    this.preview = '';
                    this.destroyCropper();
                    this.clearUploadInputs();
                },

                showPortfolioGroups() {
                    this.mode = 'portfolio';
                    if (this.activeSlot === 'cover') {
                        this.coverStep = 'portfolio-groups';
                        this.previousCoverStep = 'start';
                    } else {
                        this.profileStep = 'portfolio-groups';
                        this.previousProfileStep = 'start';
                    }
                    this.portfolioStep = 'groups';
                    this.activeGroup = null;
                    this.selectedImageId = '';
                    this.profileCropData = '';
                    this.coverCropData = '';
                    this.cropSourceReady = false;
                    this.preview = '';
                    this.destroyCropper();
                    this.clearUploadInputs();
                },

                goBackCoverStep() {
                    this.mediaError = '';
                    this.mediaStatus = '';
                    this.destroyCropper();
                    this.coverCropData = '';
                    this.cropSourceReady = false;

                    if (this.coverStep === 'crop') {
                        this.coverStep = this.previousCoverStep || 'start';

                        if (this.coverStep === 'upload') {
                            this.mode = 'upload';
                            this.selectedImageId = '';
                            this.preview = '';
                            this.clearUploadInputs();
                        }

                        if (this.coverStep === 'portfolio-images') {
                            this.mode = 'portfolio';
                            this.preview = '';
                        }

                        return;
                    }

                    if (this.coverStep === 'portfolio-images') {
                        this.coverStep = 'portfolio-groups';
                        this.activeGroup = null;
                        this.selectedImageId = '';
                        return;
                    }

                    this.coverStep = 'start';
                    this.mode = 'upload';
                    this.selectedImageId = '';
                    this.activeGroup = null;
                    this.portfolioStep = 'groups';
                    this.preview = this.coverPreview;
                    this.clearUploadInputs();
                },

                goBackProfileStep() {
                    this.mediaError = '';
                    this.mediaStatus = '';
                    this.destroyCropper();
                    this.profileCropData = '';
                    this.cropSourceReady = false;

                    if (this.profileStep === 'crop') {
                        this.profileStep = this.previousProfileStep || 'start';

                        if (this.profileStep === 'upload') {
                            this.mode = 'upload';
                            this.selectedImageId = '';
                            this.preview = '';
                            this.clearUploadInputs();
                        }

                        if (this.profileStep === 'portfolio-images') {
                            this.mode = 'portfolio';
                            this.preview = '';
                        }

                        return;
                    }

                    if (this.profileStep === 'portfolio-images') {
                        this.profileStep = 'portfolio-groups';
                        this.activeGroup = null;
                        this.selectedImageId = '';
                        return;
                    }

                    this.profileStep = 'start';
                    this.mode = 'upload';
                    this.selectedImageId = '';
                    this.activeGroup = null;
                    this.portfolioStep = 'groups';
                    this.preview = this.profilePreview;
                    this.clearUploadInputs();
                },

                initProfileCropper() {
                    if (!this.isOpen || this.activeSlot !== 'profile' || !this.cropSourceReady || !this.preview || typeof Cropper === 'undefined') {
                        return;
                    }

                    const image = this.$refs.profileCropImage;

                    if (!image || !image.complete) {
                        return;
                    }

                    if (this.cropperInitTimer) {
                        clearTimeout(this.cropperInitTimer);
                        this.cropperInitTimer = null;
                    }

                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }

                    this.cropperInitTimer = setTimeout(() => {
                        if (!this.isOpen || this.activeSlot !== 'profile' || !this.cropSourceReady || !this.$refs.profileCropImage) {
                            return;
                        }

                        this.cropper = new Cropper(this.$refs.profileCropImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.82,
                            background: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            minCropBoxWidth: 120,
                            minCropBoxHeight: 120,
                            wheelZoomRatio: 0.08,
                            ready: () => this.captureProfileCropData(),
                            crop: () => this.captureProfileCropData(),
                        });
                        this.cropperInitTimer = null;
                    }, 60);
                },

                initCoverCropper() {
                    if (!this.isOpen || this.activeSlot !== 'cover' || !this.cropSourceReady || !this.preview || typeof Cropper === 'undefined') {
                        return;
                    }

                    const image = this.$refs.coverCropImage;

                    if (!image || !image.complete) {
                        return;
                    }

                    if (this.cropperInitTimer) {
                        clearTimeout(this.cropperInitTimer);
                        this.cropperInitTimer = null;
                    }

                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }

                    this.cropperInitTimer = setTimeout(() => {
                        if (!this.isOpen || this.activeSlot !== 'cover' || !this.cropSourceReady || !this.$refs.coverCropImage) {
                            return;
                        }

                        this.cropper = new Cropper(this.$refs.coverCropImage, {
                            aspectRatio: 3,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.9,
                            background: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            minCropBoxWidth: 240,
                            minCropBoxHeight: 80,
                            wheelZoomRatio: 0.08,
                            ready: () => this.captureCoverCropData(),
                            crop: () => this.captureCoverCropData(),
                        });
                        this.cropperInitTimer = null;
                    }, 60);
                },

                captureProfileCropData() {
                    if (!this.cropper) {
                        this.profileCropData = '';
                        return;
                    }

                    this.profileCropData = JSON.stringify(this.cropper.getData(true));
                },

                captureCoverCropData() {
                    if (!this.cropper) {
                        this.coverCropData = '';
                        return;
                    }

                    this.coverCropData = JSON.stringify(this.cropper.getData(true));
                },

                destroyCropper() {
                    if (this.cropperInitTimer) {
                        clearTimeout(this.cropperInitTimer);
                        this.cropperInitTimer = null;
                    }

                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                },

                resetCropper() {
                    if (this.cropper) {
                        this.cropper.reset();
                        if (this.activeSlot === 'cover') {
                            this.captureCoverCropData();
                        } else {
                            this.captureProfileCropData();
                        }
                    }
                },

                prepareMediaSubmit() {
                    if (this.activeSlot === 'profile' && this.cropSourceReady && this.cropper && this.preview) {
                        this.captureProfileCropData();
                    }
                    if (this.activeSlot === 'cover' && this.cropSourceReady && this.cropper && this.preview) {
                        this.captureCoverCropData();
                    }
                },

                async saveMedia(event) {
                    this.prepareMediaSubmit();
                    this.isSaving = true;
                    this.mediaError = '';
                    this.mediaStatus = '';

                    try {
                        const response = await fetch(event.target.action, {
                            method: 'POST',
                            body: new FormData(event.target),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            const errors = payload.errors || {};
                            const firstError = Object.values(errors).flat()[0];
                            throw new Error(firstError || payload.message || 'The image could not be saved.');
                        }

                        if (payload.profile_photo_url) {
                            this.profilePreview = payload.profile_photo_url;
                        }

                        if (payload.cover_photo_url) {
                            this.coverPreview = payload.cover_photo_url;
                        }

                        this.preview = this.activeSlot === 'cover' ? this.coverPreview : this.profilePreview;
                        this.mediaStatus = payload.message || 'Profile media updated.';
                        this.destroyCropper();
                        this.clearUploadInputs();

                        setTimeout(() => {
                            this.close();
                        }, 450);
                    } catch (error) {
                        this.mediaError = error.message || 'The image could not be saved.';
                    } finally {
                        this.isSaving = false;
                    }
                },

                clearUploadInputs() {
                    if (this.$refs.profileUpload) {
                        this.$refs.profileUpload.value = '';
                    }

                    if (this.$refs.coverUpload) {
                        this.$refs.coverUpload.value = '';
                    }
                },
            };
        }

        function openLightbox(imageSrc) {
            document.getElementById('lightbox-image').src = imageSrc;
            document.getElementById('lightbox').classList.remove('hidden');
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
        }

        function portfolioManager(config = {}) {
            return {
                initialUploadIntent: config.initialUploadIntent,
                selectedGalleryId: config.selectedGalleryId,
                polaroidLabels: config.polaroidLabels || {},
                savedPolaroidLabels: { ...(config.polaroidLabels || {}) },
                polaroidLabelOptions: config.polaroidLabelOptions || {},
                savingPolaroidLabels: false,
                polaroidToast: '',
                polaroidToastType: 'success',
                openUploadModal(options = {}) {
                    window.dispatchEvent(new CustomEvent('configure-upload-modal', {
                        detail: {
                            mode: options.mode || 'images',
                            galleryId: options.galleryId || ''
                        }
                    }));
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upload-images' }));
                },
                init() {},
                polaroidLabelText(value) {
                    return this.polaroidLabelOptions[value] || '';
                },
                isPolaroidLabelDirty(imageId) {
                    const key = String(imageId);
                    return (this.polaroidLabels[key] || '') !== (this.savedPolaroidLabels[key] || '');
                },
                hasPolaroidLabelChanges() {
                    return Object.keys(this.polaroidLabels).some((imageId) => this.isPolaroidLabelDirty(imageId));
                },
                showPolaroidToast(message, type = 'success') {
                    this.polaroidToast = message;
                    this.polaroidToastType = type;

                    setTimeout(() => {
                        if (this.polaroidToast === message) {
                            this.polaroidToast = '';
                        }
                    }, 2500);
                },
                async savePolaroidLabels() {
                    if (!this.hasPolaroidLabelChanges()) {
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    this.savingPolaroidLabels = true;

                    try {
                        const response = await fetch(`{{ route('portfolio.polaroids.labels.update') }}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                labels: this.polaroidLabels
                            })
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(payload.message || 'Failed to update polaroid labels.');
                        }

                        this.savedPolaroidLabels = { ...this.polaroidLabels };
                        this.showPolaroidToast(payload.message || 'Polaroid labels saved.');
                    } catch (error) {
                        this.showPolaroidToast(error.message || 'Failed to update polaroid labels.', 'error');
                    } finally {
                        this.savingPolaroidLabels = false;
                    }
                },
                async deletePolaroid(imageId) {
                    if (!confirm('Delete this polaroid? This action cannot be undone.')) {
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`{{ url('/portfolio') }}/${imageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Failed to delete polaroid.');
                    }
                },
            };
        }

        document.querySelectorAll('[data-profile-bio-section]').forEach((section) => {
            const editButton = section.querySelector('[data-profile-bio-edit]');
            const cancelButton = section.querySelector('[data-profile-bio-cancel]');
            const form = section.querySelector('[data-profile-bio-form]');
            const input = section.querySelector('[data-profile-bio-input]');
            const display = section.querySelector('[data-profile-bio-display]');
            const status = section.querySelector('[data-profile-bio-status]');

            if (!form || !input || !display) {
                return;
            }

            const openEditor = () => {
                form.classList.add('is-open');
                input.focus();
            };

            const closeEditor = () => {
                form.classList.remove('is-open');
                status.textContent = '';
                status.classList.remove('is-error');
            };

            editButton?.addEventListener('click', openEditor);
            cancelButton?.addEventListener('click', closeEditor);

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                status.textContent = 'Saving...';
                status.classList.remove('is-error');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        const firstError = Object.values(errors).flat()[0];
                        throw new Error(firstError || payload.message || 'Bio could not be saved.');
                    }

                    input.value = payload.bio || '';
                    display.textContent = payload.bio || 'Add a short bio so visitors understand your style, experience, and personality.';
                    display.classList.toggle('profile-bio-empty', !payload.bio);
                    status.textContent = payload.message || 'Bio updated.';

                    setTimeout(closeEditor, 700);
                } catch (error) {
                    status.textContent = error.message || 'Bio could not be saved.';
                    status.classList.add('is-error');
                }
            });
        });

        let quickModalScrollY = 0;

        const lockQuickModalPage = () => {
            if (document.body.dataset.quickModalLocked === '1') {
                return;
            }

            quickModalScrollY = window.scrollY || document.documentElement.scrollTop || 0;
            document.body.dataset.quickModalLocked = '1';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${quickModalScrollY}px`;
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
        };

        const unlockQuickModalPage = () => {
            if (document.querySelector('[data-quick-modal].is-open')) {
                return;
            }

            const top = document.body.style.top;
            document.body.dataset.quickModalLocked = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            window.scrollTo(0, Math.abs(parseInt(top || '0', 10)) || quickModalScrollY);
        };

        const openQuickModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            lockQuickModalPage();
        };

        const closeQuickModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            unlockQuickModalPage();
        };

        document.querySelectorAll('[data-open-quick-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                openQuickModal(document.querySelector(`[data-quick-modal="${button.dataset.openQuickModal}"]`));
            });
        });

        document.querySelectorAll('[data-close-quick-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                closeQuickModal(button.closest('[data-quick-modal]'));
            });
        });

        document.querySelectorAll('[data-quick-modal]').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeQuickModal(modal);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            closeQuickModal(document.querySelector('[data-quick-modal].is-open'));
        });

        document.querySelectorAll('[data-quick-edit-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const message = form.querySelector('[data-quick-edit-message]');
                message.textContent = 'Saving...';
                message.classList.remove('is-error');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        const firstError = Object.values(errors).flat()[0];
                        throw new Error(firstError || payload.message || 'Changes could not be saved.');
                    }

                    message.textContent = payload.message || 'Updated.';
                    setTimeout(() => window.location.reload(), 450);
                } catch (error) {
                    message.textContent = error.message || 'Changes could not be saved.';
                    message.classList.add('is-error');
                }
            });
        });

    </script>
</x-app-layout>
