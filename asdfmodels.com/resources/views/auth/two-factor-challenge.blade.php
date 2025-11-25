<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
    </div>

    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf

        <!-- Code -->
        <div>
            <x-input-label for="code" :value="__('Verification Code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" maxlength="6" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
            <p class="mt-2 text-sm text-gray-600">
                Enter the 6-digit code from your authenticator app, or use a recovery code.
            </p>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

