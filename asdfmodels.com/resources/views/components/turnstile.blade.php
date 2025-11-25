@php
    $siteKey = \App\Models\Setting::getValue('cloudflare_turnstile_site_key', '');
@endphp

@if(!empty($siteKey))
    <div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-theme="light"></div>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

