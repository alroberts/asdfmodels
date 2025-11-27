<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 font-medium text-sm text-red-600 bg-red-50 p-4 rounded border-2 border-red-200">
                    <p class="font-semibold mb-2">{{ session('error') }}</p>
                    @if(session('error_details') && $devMode)
                        <div class="mt-4 p-4 bg-red-100 border border-red-300 rounded text-xs font-mono overflow-auto max-h-96">
                            <p class="font-semibold mb-2">Error Details:</p>
                            <p><strong>Message:</strong> {{ session('error_details')['message'] }}</p>
                            <p class="mt-2"><strong>File:</strong> {{ session('error_details')['file'] }}</p>
                            <p><strong>Line:</strong> {{ session('error_details')['line'] }}</p>
                            @if(isset(session('error_details')['trace']))
                                <details class="mt-2">
                                    <summary class="cursor-pointer font-semibold">Stack Trace</summary>
                                    <pre class="mt-2 whitespace-pre-wrap text-xs">{{ session('error_details')['trace'] }}</pre>
                                </details>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Image Settings -->
                        <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                            <h3 class="text-xl font-bold text-black mb-4">Image Settings</h3>
                            <div>
                                <x-input-label for="max_image_size" :value="__('Maximum Image Size (pixels)')" />
                                <x-text-input id="max_image_size" name="max_image_size" type="number" class="block mt-1 w-full" :value="old('max_image_size', $maxImageSize)" min="500" max="5000" required />
                                <p class="mt-2 text-sm text-gray-600">Maximum size in pixels on the longest edge. Images will be automatically resized to this size.</p>
                                <x-input-error :messages="$errors->get('max_image_size')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                            <h3 class="text-xl font-bold text-black mb-4">Security Settings</h3>
                            <div class="mb-6">
                                <x-input-label for="cloudflare_turnstile_site_key" :value="__('Cloudflare Turnstile Site Key')" />
                                <x-text-input id="cloudflare_turnstile_site_key" name="cloudflare_turnstile_site_key" type="text" class="block mt-1 w-full" :value="old('cloudflare_turnstile_site_key', $cloudflareSiteKey)" placeholder="Enter your Turnstile site key" />
                                <p class="mt-2 text-sm text-gray-600">Your Cloudflare Turnstile site key. Leave empty to disable Turnstile protection.</p>
                                <x-input-error :messages="$errors->get('cloudflare_turnstile_site_key')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="cloudflare_turnstile_secret_key" :value="__('Cloudflare Turnstile Secret Key')" />
                                <x-text-input id="cloudflare_turnstile_secret_key" name="cloudflare_turnstile_secret_key" type="password" class="block mt-1 w-full" placeholder="Enter your Turnstile secret key" />
                                <p class="mt-2 text-sm text-gray-600">Your Cloudflare Turnstile secret key (stored securely). Leave empty to keep existing key. Enter new key to update.</p>
                                @if(!empty($cloudflareSecretKey))
                                    <p class="mt-1 text-sm text-green-600"><i class="fas fa-check-circle"></i> Secret key is currently set</p>
                                @endif
                                <x-input-error :messages="$errors->get('cloudflare_turnstile_secret_key')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Analytics Settings -->
                        <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                            <h3 class="text-xl font-bold text-black mb-4">Analytics Settings</h3>
                            <div>
                                <x-input-label for="google_analytics_id" :value="__('Google Analytics Tracking ID')" />
                                <x-text-input id="google_analytics_id" name="google_analytics_id" type="text" class="block mt-1 w-full" :value="old('google_analytics_id', $googleAnalyticsId)" placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X" />
                                <p class="mt-2 text-sm text-gray-600">Your Google Analytics tracking ID (e.g., G-XXXXXXXXXX). Leave empty to disable analytics. Analytics will only load if users consent to cookies.</p>
                                <x-input-error :messages="$errors->get('google_analytics_id')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Developer Settings -->
                        <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                            <h3 class="text-xl font-bold text-black mb-4">Developer Settings</h3>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="dev_mode" value="1" {{ old('dev_mode', $devMode) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700 font-semibold">Enable Developer Mode</span>
                                </label>
                                <p class="mt-2 text-sm text-gray-600">When enabled, detailed error messages and stack traces will be displayed on screen instead of generic 500 errors. <strong class="text-red-600">Disable in production!</strong></p>
                            </div>
                        </div>

                        <!-- Email Settings -->
                        <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black" x-data="{ mailDriver: '{{ old('mail_driver', $mailDriver) }}' }">
                            <h3 class="text-xl font-bold text-black mb-4">Email Settings</h3>
                        
                        <!-- Email Driver -->
                        <div class="mb-6">
                            <x-input-label for="mail_driver" :value="__('Email Driver')" />
                            <select id="mail_driver" name="mail_driver" x-model="mailDriver" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" required>
                                <option value="sendmail" {{ old('mail_driver', $mailDriver) === 'sendmail' ? 'selected' : '' }}>Sendmail (PHPMail)</option>
                                <option value="smtp" {{ old('mail_driver', $mailDriver) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="ses" {{ old('mail_driver', $mailDriver) === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            </select>
                            <p class="mt-2 text-sm text-gray-600">Choose how emails are sent. Sendmail uses the server's mail system, SMTP uses an external mail server, and SES uses Amazon Simple Email Service.</p>
                            <x-input-error :messages="$errors->get('mail_driver')" class="mt-2" />
                        </div>

                        <!-- From Address & Name -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <x-input-label for="mail_from_address" :value="__('From Email Address')" />
                                <x-text-input id="mail_from_address" name="mail_from_address" type="email" class="block mt-1 w-full" :value="old('mail_from_address', $mailFromAddress)" required />
                                <x-input-error :messages="$errors->get('mail_from_address')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="mail_from_name" :value="__('From Name')" />
                                <x-text-input id="mail_from_name" name="mail_from_name" type="text" class="block mt-1 w-full" :value="old('mail_from_name', $mailFromName)" required />
                                <x-input-error :messages="$errors->get('mail_from_name')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Sendmail Settings -->
                        <div x-show="mailDriver === 'sendmail'" x-transition class="mb-6 p-4 bg-gray-50 border-2 border-gray-200 rounded-lg">
                            <h4 class="font-semibold text-black mb-3">Sendmail Configuration</h4>
                            <div class="mb-4">
                                <x-input-label for="mail_sendmail_path" :value="__('Sendmail Path')" />
                                <x-text-input id="mail_sendmail_path" name="mail_sendmail_path" type="text" class="block mt-1 w-full" :value="old('mail_sendmail_path', $mailSendmailPath)" placeholder="/usr/sbin/sendmail -bs -i" />
                                <p class="mt-2 text-sm text-gray-600">Path to sendmail executable. Default: /usr/sbin/sendmail -bs -i</p>
                                <x-input-error :messages="$errors->get('mail_sendmail_path')" class="mt-2" />
                            </div>
                        </div>

                        <!-- SMTP Settings -->
                        <div x-show="mailDriver === 'smtp'" x-transition class="mb-6 p-4 bg-gray-50 border-2 border-gray-200 rounded-lg">
                            <h4 class="font-semibold text-black mb-3">SMTP Configuration</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="mail_smtp_host" :value="__('SMTP Host')" />
                                    <x-text-input id="mail_smtp_host" name="mail_smtp_host" type="text" class="block mt-1 w-full" :value="old('mail_smtp_host', $mailSmtpHost)" placeholder="smtp.example.com" />
                                    <x-input-error :messages="$errors->get('mail_smtp_host')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="mail_smtp_port" :value="__('SMTP Port')" />
                                    <x-text-input id="mail_smtp_port" name="mail_smtp_port" type="number" class="block mt-1 w-full" :value="old('mail_smtp_port', $mailSmtpPort)" placeholder="587" />
                                    <p class="mt-1 text-sm text-gray-600">Common ports: 587 (TLS), 465 (SSL), 25 (unencrypted)</p>
                                    <x-input-error :messages="$errors->get('mail_smtp_port')" class="mt-2" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="mail_smtp_username" :value="__('SMTP Username')" />
                                    <x-text-input id="mail_smtp_username" name="mail_smtp_username" type="text" class="block mt-1 w-full" :value="old('mail_smtp_username', $mailSmtpUsername)" />
                                    <x-input-error :messages="$errors->get('mail_smtp_username')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="mail_smtp_password" :value="__('SMTP Password')" />
                                    <x-text-input id="mail_smtp_password" name="mail_smtp_password" type="password" class="block mt-1 w-full" placeholder="Leave empty to keep existing" />
                                    <p class="mt-1 text-sm text-gray-600">Leave empty to keep existing password</p>
                                    @if(!empty($mailSmtpPassword))
                                        <p class="mt-1 text-sm text-green-600"><i class="fas fa-check-circle"></i> Password is currently set</p>
                                    @endif
                                    <x-input-error :messages="$errors->get('mail_smtp_password')" class="mt-2" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="mail_smtp_encryption" :value="__('Encryption')" />
                                    <select id="mail_smtp_encryption" name="mail_smtp_encryption" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                        <option value="tls" {{ old('mail_smtp_encryption', $mailSmtpEncryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ old('mail_smtp_encryption', $mailSmtpEncryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="none" {{ old('mail_smtp_encryption', $mailSmtpEncryption) === 'none' ? 'selected' : '' }}>None</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('mail_smtp_encryption')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="mail_smtp_timeout" :value="__('Timeout (seconds)')" />
                                    <x-text-input id="mail_smtp_timeout" name="mail_smtp_timeout" type="number" class="block mt-1 w-full" :value="old('mail_smtp_timeout', $mailSmtpTimeout)" min="1" max="300" />
                                    <x-input-error :messages="$errors->get('mail_smtp_timeout')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Amazon SES Settings -->
                        <div x-show="mailDriver === 'ses'" x-transition class="mb-6 p-4 bg-gray-50 border-2 border-gray-200 rounded-lg">
                            <h4 class="font-semibold text-black mb-3">Amazon SES Configuration</h4>
                            <div class="mb-4">
                                <x-input-label for="mail_ses_region" :value="__('AWS Region')" />
                                <x-text-input id="mail_ses_region" name="mail_ses_region" type="text" class="block mt-1 w-full" :value="old('mail_ses_region', $mailSesRegion)" placeholder="us-east-1" />
                                <p class="mt-2 text-sm text-gray-600">AWS region where your SES is configured (e.g., us-east-1, eu-west-1)</p>
                                <x-input-error :messages="$errors->get('mail_ses_region')" class="mt-2" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="mail_ses_key" :value="__('AWS Access Key ID')" />
                                    <x-text-input id="mail_ses_key" name="mail_ses_key" type="text" class="block mt-1 w-full" :value="old('mail_ses_key', $mailSesKey)" />
                                    <x-input-error :messages="$errors->get('mail_ses_key')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="mail_ses_secret" :value="__('AWS Secret Access Key')" />
                                    <x-text-input id="mail_ses_secret" name="mail_ses_secret" type="password" class="block mt-1 w-full" placeholder="Leave empty to keep existing" />
                                    <p class="mt-1 text-sm text-gray-600">Leave empty to keep existing secret</p>
                                    @if(!empty($mailSesSecret))
                                        <p class="mt-1 text-sm text-green-600"><i class="fas fa-check-circle"></i> Secret key is currently set</p>
                                    @endif
                                    <x-input-error :messages="$errors->get('mail_ses_secret')" class="mt-2" />
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> Make sure your AWS credentials have SES permissions. For bounce/complaint handling, configure SNS topics in your AWS console.
                                </p>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="mt-6 flex items-center justify-end">
                    <x-primary-button>
                        {{ __('Save Settings') }}
                    </x-primary-button>
                </div>
            </form>

            <!-- Test Email Section (Outside main form) -->
            <div class="mt-6 bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                <h3 class="text-xl font-bold text-black mb-4">Test Email Configuration</h3>
                <form method="POST" action="{{ route('admin.settings.test-email') }}" class="flex gap-2">
                    @csrf
                    <x-text-input type="email" name="test_email" placeholder="Enter email address to test" class="flex-1" required />
                    <x-primary-button type="submit">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </x-primary-button>
                </form>
                <p class="mt-2 text-sm text-gray-600">Send a test email to verify your email configuration is working correctly. Make sure to save your email settings first.</p>
            </div>
        </div>
    </div>
</x-app-layout>

