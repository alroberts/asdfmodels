<?php

namespace App\Services;

use App\Models\FeedPost;
use App\Models\FeedPostMention;
use App\Models\PortfolioAlbum;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FeedPostService
{
    public function createPost(User $user, array $data, array $images = []): FeedPost
    {
        $body = $this->sanitiseBody((string) ($data['body'] ?? ''), $user);
        $linkUrl = $this->normaliseLink($data['link_url'] ?? null);

        if ($linkUrl && !$this->canShareLink($user, $linkUrl)) {
            throw ValidationException::withMessages([
                'link_url' => 'External links are available to verified members. ASDF Models links are always allowed.',
            ]);
        }

        $preview = $linkUrl ? $this->fetchLinkPreview($linkUrl) : [];

        $post = FeedPost::create([
            'user_id' => $user->id,
            'type' => FeedPost::TYPE_POST,
            'body' => $data['body'] ?? null,
            'display_body' => $body,
            'link_url' => $linkUrl,
            'link_title' => $preview['title'] ?? null,
            'link_description' => $preview['description'] ?? null,
            'link_image' => $preview['image'] ?? null,
            'visibility' => 'connections',
        ]);

        $this->storeImages($post, $images);
        $this->syncMentions($post, $user, (string) ($data['body'] ?? ''));

        return $post->load(['user.modelProfile', 'user.photographerProfile', 'images', 'mentions.mentionedUser']);
    }

    public function createGalleryPost(User $user, PortfolioAlbum $gallery): FeedPost
    {
        $url = route('public.galleries.show', $gallery);
        $cover = $gallery->cover_image_path ?: $gallery->coverImage?->thumbnail_path ?: $gallery->coverImage?->full_path;

        $post = FeedPost::create([
            'user_id' => $user->id,
            'type' => FeedPost::TYPE_GALLERY,
            'body' => null,
            'display_body' => 'Shared a new gallery.',
            'link_url' => $url,
            'link_title' => $gallery->name,
            'link_description' => $gallery->description,
            'link_image' => $cover ? asset($cover) : null,
            'related_type' => PortfolioAlbum::class,
            'related_id' => $gallery->id,
            'visibility' => 'connections',
        ]);

        return $post;
    }

    public function previewLink(User $user, ?string $url): array
    {
        $linkUrl = $this->normaliseLink($url);

        if (!$linkUrl) {
            throw ValidationException::withMessages([
                'link_url' => 'Enter a valid link.',
            ]);
        }

        if (!$this->canShareLink($user, $linkUrl)) {
            throw ValidationException::withMessages([
                'link_url' => 'External links are available to verified members. ASDF Models links are always allowed.',
            ]);
        }

        $preview = $this->fetchLinkPreview($linkUrl);

        return [
            'url' => $linkUrl,
            'host' => parse_url($linkUrl, PHP_URL_HOST),
            'title' => $preview['title'] ?? parse_url($linkUrl, PHP_URL_HOST),
            'description' => $preview['description'] ?? null,
            'image' => $preview['image'] ?? null,
        ];
    }

    public function sanitiseBody(string $body, User $user): string
    {
        $body = strip_tags($body);
        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $body = preg_replace('/[ \t]+\n/', "\n", $body) ?? $body;
        $body = preg_replace('/\n{3,}/', "\n\n", $body) ?? $body;
        $body = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email hidden]', $body) ?? $body;
        $body = preg_replace('/(?<!\w)(?:\+?\d[\d\s().-]{7,}\d)(?!\w)/', '[phone hidden]', $body) ?? $body;

        if (!$this->isVerified($user)) {
            $body = preg_replace_callback('/https?:\/\/[^\s<]+/i', function (array $matches) {
                return $this->isInternalUrl($matches[0]) ? $matches[0] : '[external link hidden]';
            }, $body) ?? $body;
        }

        $fullName = trim($user->full_name);
        if ($fullName !== '' && $fullName !== $user->display_name) {
            $body = preg_replace('/\b' . preg_quote($fullName, '/') . '\b/u', $user->display_name, $body) ?? $body;
        }

        return trim($body);
    }

    public function syncMentions(FeedPost $post, User $actor, string $body): void
    {
        preg_match_all('/@([a-z0-9][a-z0-9-]{1,60})/i', $body, $matches);
        $usernames = collect($matches[1] ?? [])->map(fn ($username) => Str::lower($username))->unique();

        $users = $usernames->isEmpty()
            ? collect()
            : User::whereIn('username', $usernames)->get();

        foreach ($users as $mentionedUser) {
            if ((int) $mentionedUser->id === (int) $actor->id) {
                continue;
            }

            $mention = FeedPostMention::firstOrCreate(
                [
                    'feed_post_id' => $post->id,
                    'mentioned_user_id' => $mentionedUser->id,
                ],
                [
                    'mentioned_by_user_id' => $actor->id,
                    'mention_handle' => $mentionedUser->username,
                    'status' => FeedPostMention::STATUS_PENDING,
                ]
            );

            \App\Models\SiteNotification::notifyFeedMention($mention->loadMissing(['post', 'mentionedBy']));
        }
    }

    private function storeImages(FeedPost $post, array $images): void
    {
        $folder = public_path("uploads/feed/{$post->user_id}");
        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        foreach (array_values($images) as $index => $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }

            $extension = strtolower($image->getClientOriginalExtension());
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? ($extension === 'jpeg' ? 'jpg' : $extension) : 'jpg';
            $filename = 'feed_' . uniqid() . '.' . $extension;
            $image->move($folder, $filename);

            $post->images()->create([
                'path' => "uploads/feed/{$post->user_id}/{$filename}",
                'display_order' => $index,
            ]);
        }
    }

    private function normaliseLink(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function canShareLink(User $user, string $url): bool
    {
        return $this->isInternalUrl($url) || $this->isVerified($user);
    }

    private function isInternalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array(Str::lower((string) $host), ['asdfmodels.com', 'www.asdfmodels.com'], true);
    }

    private function isVerified(User $user): bool
    {
        return (bool) ($user->is_photographer
            ? $user->photographerProfile?->isVerified()
            : $user->modelProfile?->isVerified());
    }

    private function fetchLinkPreview(string $url): array
    {
        if (!$this->isInternalUrl($url) && !Str::startsWith($url, 'https://')) {
            return [];
        }

        try {
            $response = Http::timeout(4)->get($url);
            if (!$response->ok()) {
                return [];
            }

            $html = $response->body();

            return [
                'title' => $this->metaValue($html, 'og:title') ?: $this->titleValue($html),
                'description' => $this->metaValue($html, 'og:description') ?: $this->metaValue($html, 'description'),
                'image' => $this->metaValue($html, 'og:image'),
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    private function metaValue(string $html, string $name): ?string
    {
        if (!preg_match_all('/<meta\b[^>]*>/i', $html, $tags)) {
            return null;
        }

        foreach ($tags[0] as $tag) {
            preg_match_all('/([a-zA-Z_:.-]+)\s*=\s*["\']([^"\']*)["\']/', $tag, $attrs);
            $attributes = collect($attrs[1] ?? [])
                ->mapWithKeys(fn ($attribute, $index) => [Str::lower($attribute) => $attrs[2][$index] ?? '']);

            $metaName = $attributes->get('property') ?: $attributes->get('name');
            if (!is_string($metaName) || Str::lower($metaName) !== Str::lower($name)) {
                continue;
            }

            $content = $attributes->get('content');
            if (is_string($content) && $content !== '') {
                return html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
            }
        }

        return null;
    }

    private function titleValue(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5));
        }

        return null;
    }
}
