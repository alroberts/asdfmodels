<x-app-layout>
    @push('styles')
        <style>
            .model-galleries-page {
                margin: 0 auto;
                max-width: 1180px;
                padding: 48px 24px;
            }

            .model-galleries-header {
                align-items: flex-end;
                display: flex;
                gap: 24px;
                justify-content: space-between;
                margin-bottom: 28px;
            }

            .model-galleries-kicker {
                color: #6b7280;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.22em;
                text-transform: uppercase;
            }

            .model-galleries-title {
                color: #050505;
                font-size: clamp(32px, 5vw, 54px);
                font-weight: 800;
                line-height: 0.95;
                margin: 8px 0 0;
            }

            .model-galleries-back {
                align-items: center;
                border: 1px solid #d1d5db;
                border-radius: 999px;
                color: #111827;
                display: inline-flex;
                font-size: 14px;
                font-weight: 700;
                gap: 8px;
                padding: 10px 14px;
                text-decoration: none;
            }

            .model-galleries-grid {
                display: grid;
                gap: 18px;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }

            .model-gallery-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 18px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
                color: inherit;
                display: block;
                overflow: hidden;
                text-decoration: none;
                transition: border-color .18s ease, transform .18s ease;
            }

            .model-gallery-card:hover {
                border-color: #111827;
                transform: translateY(-2px);
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
                width: 100%;
            }

            .model-gallery-body {
                padding: 16px;
            }

            .model-gallery-name {
                color: #111827;
                font-size: 17px;
                font-weight: 800;
                margin: 0;
            }

            .model-gallery-meta {
                color: #6b7280;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.08em;
                margin-top: 8px;
                text-transform: uppercase;
            }

            .model-gallery-empty {
                background: #fff;
                border: 1px dashed #d1d5db;
                border-radius: 18px;
                color: #6b7280;
                padding: 56px 24px;
                text-align: center;
            }

            .model-gallery-empty-icon {
                font-size: 30px;
            }

            .model-gallery-empty-text {
                font-size: 14px;
                font-weight: 700;
                margin-top: 12px;
            }

            .model-gallery-description {
                color: #4b5563;
                font-size: 14px;
                line-height: 1.6;
                margin-top: 12px;
            }

            @media (max-width: 720px) {
                .model-galleries-header {
                    align-items: flex-start;
                    flex-direction: column;
                }
            }
        </style>
    @endpush

    <main class="model-galleries-page">
        <header class="model-galleries-header">
            <div>
                <p class="model-galleries-kicker">{{ $profile->display_name }}</p>
                <h1 class="model-galleries-title">Galleries</h1>
            </div>
            <a href="{{ route('models.show', $user->profileRouteIdentifier()) }}" class="model-galleries-back">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Profile</span>
            </a>
        </header>

        @if($publicGalleries->isNotEmpty())
            <section class="model-galleries-grid">
                @foreach($publicGalleries as $gallery)
                    @php
                        $galleryCover = $gallery->cover_image_path
                            ?? $gallery->coverImage?->thumbnail_path
                            ?? $gallery->images->first()?->thumbnail_path;
                    @endphp
                    <a id="gallery-{{ $gallery->id }}" href="{{ route('public.galleries.show', $gallery->id) }}" class="model-gallery-card">
                        <div class="model-gallery-thumb">
                            @if($galleryCover)
                                <img src="{{ asset($galleryCover) }}" alt="{{ $gallery->name }}">
                            @else
                                <div class="model-gallery-empty">
                                    <i class="fas fa-images model-gallery-empty-icon"></i>
                                </div>
                            @endif
                        </div>
                        <div class="model-gallery-body">
                            <h2 class="model-gallery-name">{{ $gallery->name }}</h2>
                            <p class="model-gallery-meta">{{ $gallery->images_count }} {{ $gallery->images_count === 1 ? 'image' : 'images' }}</p>
                            @if($gallery->description)
                                <p class="model-gallery-description">{{ $gallery->description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </section>
        @else
            <section class="model-gallery-empty">
                <i class="fas fa-images model-gallery-empty-icon"></i>
                <p class="model-gallery-empty-text">No public galleries yet.</p>
            </section>
        @endif
    </main>
</x-app-layout>
