<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Confirm Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @if ($setup['method'] === 'authenticator')
                        <div class="mb-4">
                            <h3 class="font-semibold mb-2">Scan QR Code</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)
                            </p>
                            <div class="mb-4">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($setup['qr_code_url']) }}" alt="QR Code" class="mx-auto border border-gray-200 rounded">
                            </div>
                            <p class="text-sm text-gray-600 mb-4">
                                Or enter this secret key manually: <code class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $setup['secret'] }}</code>
                            </p>
                        </div>
                    @elseif ($setup['method'] === 'email')
                        <div class="mb-4">
                            <h3 class="font-semibold mb-2">Check Your Email</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                We've sent a 6-digit verification code to your email address. Please enter it below.
                            </p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('two-factor.verify') }}">
                        @csrf

                        <div>
                            <x-input-label for="code" :value="__('Verification Code')" />
                            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            <p class="mt-2 text-sm text-gray-600">
                                Enter the 6-digit code from your {{ $setup['method'] === 'authenticator' ? 'authenticator app' : 'email' }}.
                            </p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Verify and Enable') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

