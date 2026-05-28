@php
    $author = $post->user;
    $profile = $author?->is_photographer ? $author?->photographerProfile : $author?->modelProfile;
    $avatar = $profile?->profile_photo_path;
    $profileRoute = $author?->is_photographer
        ? route('photographers.show', $author->profileRouteIdentifier())
        : route('models.show', $author->profileRouteIdentifier());
    $gallery = $post->related instanceof \App\Models\PortfolioAlbum ? $post->related : null;
    $galleryCover = $gallery
        ? ($gallery->cover_image_path ?? $gallery->coverImage?->thumbnail_path ?? $gallery->coverImage?->full_path ?? $gallery->images?->first()?->thumbnail_path)
        : null;
    $linkImage = $post->link_image ?: ($galleryCover ? asset($galleryCover) : null);
    $linkTitle = $post->link_title ?: $gallery?->name ?: parse_url($post->link_url ?? '', PHP_URL_HOST);
    $linkDescription = $post->link_description ?: $gallery?->description;
    $mentionMap = collect($post->mentions ?? [])
        ->filter(fn ($mention) => $mention->mentionedUser && $mention->mentionedUser->username)
        ->flatMap(function ($mention) {
            $user = $mention->mentionedUser;
            $profileUrl = $user->is_photographer
                ? route('photographers.show', $user->profileRouteIdentifier())
                : route('models.show', $user->profileRouteIdentifier());

            return collect([$mention->mention_handle, $user->username])
                ->filter()
                ->mapWithKeys(fn ($handle) => [
                    \Illuminate\Support\Str::lower($handle) => [
                        'label' => '@' . $user->username,
                        'url' => $profileUrl,
                    ],
                ]);
        });

    $renderFeedBody = function (?string $body) use ($mentionMap): string {
        $body = (string) $body;
        $parts = preg_split('/(@[a-z0-9][a-z0-9-]{1,60})/i', $body, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($parts)->map(function ($part) use ($mentionMap) {
            if (preg_match('/^@([a-z0-9][a-z0-9-]{1,60})$/i', $part, $matches)) {
                $mention = $mentionMap->get(\Illuminate\Support\Str::lower($matches[1]));

                if ($mention) {
                    return '<a class="feed-inline-mention" href="' . e($mention['url']) . '">' . e($mention['label']) . '</a>';
                }
            }

            return e($part);
        })->join('');
    };
@endphp

@once
    @push('styles')
        <style>
            .feed-card {
                border: 1px solid #e5e7eb;
                background: #fff;
                border-radius: 24px;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
            }

            .feed-post {
                padding: 22px;
            }

            .feed-author {
                align-items: center;
                color: #111827;
                display: flex;
                gap: 12px;
                text-decoration: none;
            }

            .feed-author strong {
                display: block;
                font-size: 15px;
            }

            .feed-author span span {
                color: #6b7280;
                display: block;
                font-size: 13px;
                margin-top: 2px;
            }

            .feed-avatar {
                align-items: center;
                background: #111827;
                border-radius: 999px;
                color: #fff;
                display: inline-flex;
                flex: 0 0 44px;
                font-weight: 700;
                height: 44px;
                justify-content: center;
                overflow: hidden;
                width: 44px;
            }

            .feed-avatar img {
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .feed-body {
                color: #1f2937;
                font-size: 15px;
                line-height: 1.65;
                margin-top: 16px;
            }

            .feed-inline-mention {
                color: #111827;
                font-weight: 800;
                text-decoration: none;
            }

            .feed-inline-mention:hover {
                text-decoration: underline;
            }

            .feed-images {
                display: grid;
                gap: 10px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-top: 16px;
            }

            .feed-images img {
                aspect-ratio: 1 / 1;
                border-radius: 18px;
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .feed-link-card {
                align-items: stretch;
                border: 1px solid #e5e7eb;
                border-radius: 20px;
                color: inherit;
                display: grid;
                grid-template-columns: 150px minmax(0, 1fr);
                margin-top: 16px;
                overflow: hidden;
                text-decoration: none;
            }

            .feed-link-card > img,
            .feed-link-placeholder {
                background: #f3f4f6;
                height: 100%;
                min-height: 120px;
                object-fit: cover;
                width: 100%;
            }

            .feed-link-body {
                padding: 16px;
            }

            .feed-link-body strong {
                display: block;
                font-size: 15px;
                line-height: 1.35;
            }

            .feed-link-body p {
                color: #6b7280;
                font-size: 13px;
                line-height: 1.45;
                margin: 6px 0 0;
            }

            .feed-muted {
                color: #6b7280;
                font-size: 13px;
            }

            .feed-profile-section {
                display: grid;
                gap: 18px;
            }

            .feed-profile-list {
                display: grid;
                gap: 16px;
            }

            @media (max-width: 720px) {
                .feed-link-card {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush
@endonce

<article class="feed-card feed-post">
    <a class="feed-author" href="{{ $profileRoute }}">
        <span class="feed-avatar">
            @if($avatar)
                <img src="{{ asset($avatar) }}" alt="{{ $author->display_name }}">
            @else
                {{ mb_substr($author->display_name ?: $author->name, 0, 1) }}
            @endif
        </span>
        <span>
            <strong>{{ $profile?->display_name ?? $author->display_name }}</strong>
            <span>{{ '@' . $author->username }} · {{ $post->created_at->diffForHumans() }}</span>
        </span>
    </a>

    @if($post->display_body)
        <div class="feed-body">{!! nl2br($renderFeedBody($post->display_body)) !!}</div>
    @endif

    @if($post->images->isNotEmpty())
        <div class="feed-images">
            @foreach($post->images->take(4) as $image)
                <img src="{{ asset($image->path) }}" alt="">
            @endforeach
        </div>
    @endif

    @if($post->link_url)
        <a class="feed-link-card" href="{{ $post->link_url }}" target="_blank" rel="noopener">
            @if($linkImage)
                <img src="{{ $linkImage }}" alt="">
            @else
                <span class="feed-link-placeholder"></span>
            @endif
            <span class="feed-link-body">
                <strong>{{ $linkTitle }}</strong>
                @if($linkDescription)
                    <p>{{ \Illuminate\Support\Str::limit($linkDescription, 150) }}</p>
                @endif
                <p>{{ parse_url($post->link_url, PHP_URL_HOST) }}</p>
            </span>
        </a>
    @endif

    @if($post->mentions->isNotEmpty())
        <p class="feed-muted mt-4">
            Featuring
            {!! $post->mentions->filter(fn($mention) => $mention->mentionedUser?->username)->map(function ($mention) {
                $user = $mention->mentionedUser;
                $profileUrl = $user->is_photographer
                    ? route('photographers.show', $user->profileRouteIdentifier())
                    : route('models.show', $user->profileRouteIdentifier());

                return '<a class="feed-inline-mention" href="' . e($profileUrl) . '">@' . e($user->username) . '</a>';
            })->join(', ') !!}
        </p>
    @endif
</article>
