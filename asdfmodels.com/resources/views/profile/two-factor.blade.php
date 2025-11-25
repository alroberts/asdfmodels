<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('recovery_codes'))
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                            <h3 class="font-semibold text-yellow-800 mb-2">Recovery Codes</h3>
                            <p class="text-sm text-yellow-700 mb-2">Please save these recovery codes in a safe place. You can use them to access your account if you lose access to your authenticator device.</p>
                            <div class="font-mono text-sm space-y-1">
                                @foreach (session('recovery_codes') as $code)
                                    <div>{{ $code }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($user->hasTwoFactorEnabled())
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-4">
                                Two-factor authentication is <strong class="text-green-600">enabled</strong> using 
                                <strong>{{ $user->two_factor_method === 'authenticator' ? 'Authenticator App' : 'Email' }}</strong>.
                            </p>
                            
                            <form method="POST" action="{{ route('two-factor.disable') }}">
                                @csrf
                                @method('DELETE')
                                
                                <x-primary-button class="bg-red-600 hover:bg-red-700">
                                    {{ __('Disable Two-Factor Authentication') }}
                                </x-primary-button>
                            </form>
                        </div>
                    @else
                        <div class="space-y-4">
                            <p class="text-sm text-gray-600 mb-4">
                                Add an additional layer of security to your account by enabling two-factor authentication.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <form method="POST" action="{{ route('two-factor.authenticator') }}">
                                    @csrf
                                    <div class="p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition">
                                        <h3 class="font-semibold mb-2">Authenticator App</h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            Use an authenticator app like Google Authenticator or Authy to generate codes.
                                        </p>
                                        <x-primary-button>
                                            {{ __('Enable Authenticator') }}
                                        </x-primary-button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('two-factor.email') }}">
                                    @csrf
                                    <div class="p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition">
                                        <h3 class="font-semibold mb-2">Email</h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            Receive verification codes via email.
                                        </p>
                                        <x-primary-button>
                                            {{ __('Enable Email') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

