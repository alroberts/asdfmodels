<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Message') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('messages.store') }}">
                    @csrf

                    <div class="mb-6">
                        <x-input-label for="recipient_id" :value="__('To')" />
                        @if(isset($recipient))
                            <input type="hidden" name="recipient_id" value="{{ $recipient->id }}">
                            <div class="mt-2 p-3 bg-gray-50 rounded-md border-2 border-black">
                                <div class="flex items-center space-x-3">
                                    @if($recipient->modelProfile && $recipient->modelProfile->profile_photo_path)
                                        <img src="{{ asset($recipient->modelProfile->profile_photo_path) }}" alt="{{ $recipient->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-lg text-gray-600">{{ substr($recipient->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <span class="font-semibold">{{ $recipient->name }}</span>
                                </div>
                            </div>
                        @else
                            <select id="recipient_id" name="recipient_id" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" required>
                                <option value="">Select a user...</option>
                                @php
                                    $users = \App\Models\User::where('id', '!=', Auth::id())
                                        ->where('is_admin', false)
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <x-input-error :messages="$errors->get('recipient_id')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="body" :value="__('Message')" />
                        <textarea id="body" name="body" rows="6" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" placeholder="Type your message..." required>{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>

                    @auth
                        @php
                            $userImages = \App\Models\PortfolioImage::where('model_id', Auth::id())
                                ->orWhere('photographer_id', Auth::id())
                                ->where('is_public', true)
                                ->orderBy('created_at', 'desc')
                                ->limit(20)
                                ->get();
                        @endphp
                        @if($userImages->count() > 0)
                            <div class="mb-6">
                                <x-input-label for="portfolio_image_id" :value="__('Attach Image (optional)')" />
                                <select id="portfolio_image_id" name="portfolio_image_id" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                    <option value="">None</option>
                                    @foreach($userImages as $image)
                                        <option value="{{ $image->id }}">{{ $image->title ?: 'Image #' . $image->id }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-600">Attach one of your portfolio images to this message.</p>
                                <x-input-error :messages="$errors->get('portfolio_image_id')" class="mt-2" />
                            </div>
                        @endif
                    @endauth

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('messages.index') }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
                        <x-primary-button>
                            {{ __('Send Message') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

