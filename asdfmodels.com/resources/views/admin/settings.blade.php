<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('patch')

                    <!-- Image Settings -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-black mb-4">Image Settings</h3>
                        <div class="mb-6">
                            <x-input-label for="max_image_size" :value="__('Maximum Image Size (pixels)')" />
                            <x-text-input id="max_image_size" name="max_image_size" type="number" class="block mt-1 w-full" :value="old('max_image_size', $maxImageSize)" min="500" max="5000" required />
                            <p class="mt-2 text-sm text-gray-600">Maximum size in pixels on the longest edge. Images will be automatically resized to this size.</p>
                            <x-input-error :messages="$errors->get('max_image_size')" class="mt-2" />
                        </div>
                    </div>

                    <div class="border-t-2 border-gray-200 my-8"></div>

                    <!-- Security Settings -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-black mb-4">Security Settings</h3>
                        <div class="mb-6">
                            <x-input-label for="cloudflare_turnstile_site_key" :value="__('Cloudflare Turnstile Site Key')" />
                            <x-text-input id="cloudflare_turnstile_site_key" name="cloudflare_turnstile_site_key" type="text" class="block mt-1 w-full" :value="old('cloudflare_turnstile_site_key', $cloudflareSiteKey)" placeholder="Enter your Turnstile site key" />
                            <p class="mt-2 text-sm text-gray-600">Your Cloudflare Turnstile site key. Leave empty to disable Turnstile protection.</p>
                            <x-input-error :messages="$errors->get('cloudflare_turnstile_site_key')" class="mt-2" />
                        </div>
                        <div class="mb-6">
                            <x-input-label for="cloudflare_turnstile_secret_key" :value="__('Cloudflare Turnstile Secret Key')" />
                            <x-text-input id="cloudflare_turnstile_secret_key" name="cloudflare_turnstile_secret_key" type="password" class="block mt-1 w-full" placeholder="Enter your Turnstile secret key" />
                            <p class="mt-2 text-sm text-gray-600">Your Cloudflare Turnstile secret key (stored securely). Leave empty to keep existing key. Enter new key to update.</p>
                            @if(!empty($cloudflareSecretKey))
                                <p class="mt-1 text-sm text-green-600"><i class="fas fa-check-circle"></i> Secret key is currently set</p>
                            @endif
                            <x-input-error :messages="$errors->get('cloudflare_turnstile_secret_key')" class="mt-2" />
                        </div>
                    </div>

                    <div class="border-t-2 border-gray-200 my-8"></div>

                    <!-- Analytics Settings -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-black mb-4">Analytics Settings</h3>
                        <div class="mb-6">
                            <x-input-label for="google_analytics_id" :value="__('Google Analytics Tracking ID')" />
                            <x-text-input id="google_analytics_id" name="google_analytics_id" type="text" class="block mt-1 w-full" :value="old('google_analytics_id', $googleAnalyticsId)" placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X" />
                            <p class="mt-2 text-sm text-gray-600">Your Google Analytics tracking ID (e.g., G-XXXXXXXXXX). Leave empty to disable analytics. Analytics will only load if users consent to cookies.</p>
                            <x-input-error :messages="$errors->get('google_analytics_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>
                            {{ __('Save Settings') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

