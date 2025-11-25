@php
    try {
        $cloudflareEnabled = !empty(\App\Models\Setting::getValue('cloudflare_turnstile_site_key', ''));
        $analyticsEnabled = !empty(\App\Models\Setting::getValue('google_analytics_id', ''));
    } catch (\Exception $e) {
        $cloudflareEnabled = false;
        $analyticsEnabled = false;
    }
@endphp

<div id="cookie-consent-banner" x-data="{ 
    show: false,
    showDetails: false,
    essential: true,
    analytics: false,
    marketing: false,
    init() {
        // Check if consent has been given
        const consent = this.getCookie('cookie_consent');
        if (!consent) {
            this.show = true;
        } else {
            const preferences = JSON.parse(consent);
            this.essential = preferences.essential !== false;
            this.analytics = preferences.analytics === true;
            this.marketing = preferences.marketing === true;
            this.loadScripts(preferences);
        }
    },
    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    },
    setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
    },
    acceptAll() {
        const preferences = {
            essential: true,
            analytics: true,
            marketing: true,
            timestamp: new Date().toISOString()
        };
        this.setCookie('cookie_consent', JSON.stringify(preferences), 365);
        this.show = false;
        this.loadScripts(preferences);
    },
    acceptSelected() {
        const preferences = {
            essential: this.essential,
            analytics: this.analytics,
            marketing: this.marketing,
            timestamp: new Date().toISOString()
        };
        this.setCookie('cookie_consent', JSON.stringify(preferences), 365);
        this.show = false;
        this.loadScripts(preferences);
    },
    rejectAll() {
        const preferences = {
            essential: true,
            analytics: false,
            marketing: false,
            timestamp: new Date().toISOString()
        };
        this.setCookie('cookie_consent', JSON.stringify(preferences), 365);
        this.show = false;
        this.loadScripts(preferences);
    },
    loadScripts(preferences) {
        // Load Google Analytics if enabled and consented
        @if($analyticsEnabled)
        if (preferences.analytics) {
            const gaId = '{{ \App\Models\Setting::getValue("google_analytics_id", "") }}';
            if (gaId) {
                // Google Analytics 4
                if (gaId.startsWith('G-')) {
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', gaId);
                    
                    const script1 = document.createElement('script');
                    script1.async = true;
                    script1.src = 'https://www.googletagmanager.com/gtag/js?id=' + gaId;
                    document.head.appendChild(script1);
                    
                    const script2 = document.createElement('script');
                    script2.innerHTML = `window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '${gaId}');`;
                    document.head.appendChild(script2);
                }
                // Universal Analytics (legacy)
                else if (gaId.startsWith('UA-')) {
                    window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
                    ga('create', gaId, 'auto');
                    ga('send', 'pageview');
                    
                    const script = document.createElement('script');
                    script.async = true;
                    script.src = 'https://www.google-analytics.com/analytics.js';
                    document.head.appendChild(script);
                }
            }
        }
        @endif
    }
}" 
x-show="show" 
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0 translate-y-4"
x-transition:enter-end="opacity-100 translate-y-0"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100 translate-y-0"
x-transition:leave-end="opacity-0 translate-y-4"
class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t-2 border-black shadow-lg p-4 md:p-6"
style="display: none;">
    <div class="max-w-7xl mx-auto">
        <div x-show="!showDetails">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-black mb-2">Cookie Consent</h3>
                    <p class="text-sm text-gray-700">
                        We use cookies to enhance your browsing experience, analyze site traffic, and personalize content. 
                        By clicking "Accept All", you consent to our use of cookies. You can also customize your preferences or reject non-essential cookies.
                        <a href="{{ route('legal.cookies') }}" class="text-black underline hover:text-gray-600">Learn more</a>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="showDetails = true" class="px-4 py-2 text-sm font-medium text-black border-2 border-black hover:bg-gray-100 transition">
                        Customize
                    </button>
                    <button @click="rejectAll()" class="px-4 py-2 text-sm font-medium text-black border-2 border-black hover:bg-gray-100 transition">
                        Reject All
                    </button>
                    <button @click="acceptAll()" class="px-4 py-2 text-sm font-medium text-white bg-black hover:bg-gray-800 transition">
                        Accept All
                    </button>
                </div>
            </div>
        </div>

        <div x-show="showDetails" x-transition>
            <h3 class="text-lg font-bold text-black mb-4">Cookie Preferences</h3>
            <p class="text-sm text-gray-700 mb-4">
                Select which types of cookies you want to accept. Essential cookies are required for the website to function and cannot be disabled.
            </p>

            <div class="space-y-4 mb-6">
                <!-- Essential Cookies -->
                <div class="flex items-start justify-between p-4 border-2 border-black">
                    <div class="flex-1">
                        <h4 class="font-semibold text-black mb-1">Essential Cookies</h4>
                        <p class="text-sm text-gray-600">Required for the website to function properly. These cannot be disabled.</p>
                    </div>
                    <input type="checkbox" disabled checked class="ml-4 rounded border-gray-300 text-black focus:ring-black" />
                </div>

                <!-- Analytics Cookies -->
                @if($analyticsEnabled)
                <div class="flex items-start justify-between p-4 border-2 border-gray-300">
                    <div class="flex-1">
                        <h4 class="font-semibold text-black mb-1">Analytics Cookies</h4>
                        <p class="text-sm text-gray-600">Help us understand how visitors interact with our website by collecting and reporting information anonymously.</p>
                    </div>
                    <input type="checkbox" x-model="analytics" class="ml-4 rounded border-gray-300 text-black focus:ring-black" />
                </div>
                @endif

                <!-- Marketing Cookies (placeholder for future use) -->
                <div class="flex items-start justify-between p-4 border-2 border-gray-300 opacity-50">
                    <div class="flex-1">
                        <h4 class="font-semibold text-black mb-1">Marketing Cookies</h4>
                        <p class="text-sm text-gray-600">Used to track visitors across websites for marketing purposes. Currently not in use.</p>
                    </div>
                    <input type="checkbox" disabled class="ml-4 rounded border-gray-300 text-black focus:ring-black" />
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button @click="showDetails = false" class="px-4 py-2 text-sm font-medium text-black border-2 border-black hover:bg-gray-100 transition">
                    Back
                </button>
                <button @click="rejectAll()" class="px-4 py-2 text-sm font-medium text-black border-2 border-black hover:bg-gray-100 transition">
                    Reject All
                </button>
                <button @click="acceptSelected()" class="px-4 py-2 text-sm font-medium text-white bg-black hover:bg-gray-800 transition">
                    Save Preferences
                </button>
            </div>
        </div>
    </div>
</div>

