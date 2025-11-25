<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('messages.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex items-center space-x-3">
                @if($otherUser->modelProfile && $otherUser->modelProfile->profile_photo_path)
                    <img src="{{ asset($otherUser->modelProfile->profile_photo_path) }}" alt="{{ $otherUser->name }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-lg text-gray-600">{{ substr($otherUser->name, 0, 1) }}</span>
                    </div>
                @endif
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $otherUser->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Messages -->
            <div class="bg-white shadow sm:rounded-lg mb-6" style="height: 500px; overflow-y: auto;">
                <div class="p-6 space-y-4">
                    @foreach($messages as $message)
                        <div class="flex {{ $message->sender_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-md {{ $message->sender_id === Auth::id() ? 'bg-black text-white' : 'bg-gray-100 text-black' }} rounded-lg p-4">
                                @if($message->portfolioImage)
                                    <div class="mb-3">
                                        <a href="{{ asset($message->portfolioImage->full_path) }}" target="_blank">
                                            <img src="{{ asset($message->portfolioImage->medium_path) }}" alt="Attachment" class="max-w-full h-auto rounded border-2 {{ $message->sender_id === Auth::id() ? 'border-white' : 'border-black' }}">
                                        </a>
                                    </div>
                                @endif
                                <p class="text-sm">{{ $message->body }}</p>
                                <p class="text-xs mt-2 opacity-75">{{ $message->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Message Form -->
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('messages.store') }}">
                    @csrf
                    <input type="hidden" name="thread_id" value="{{ $thread->id }}">
                    <input type="hidden" name="recipient_id" value="{{ $otherUser->id }}">
                    
                    <div class="mb-4">
                        <textarea name="body" rows="3" class="w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" placeholder="Type your message..." required></textarea>
                    </div>
                    
                    @php
                        $userImages = \App\Models\PortfolioImage::where('model_id', Auth::id())
                            ->orWhere('photographer_id', Auth::id())
                            ->where('is_public', true)
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();
                    @endphp
                    @if($userImages->count() > 0)
                        <div class="mb-4">
                            <select name="portfolio_image_id" class="w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">No attachment</option>
                                @foreach($userImages as $image)
                                    <option value="{{ $image->id }}">{{ $image->title ?: 'Image #' . $image->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="flex justify-end">
                        <x-primary-button>
                            Send
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom
        window.addEventListener('load', function() {
            const messagesContainer = document.querySelector('.bg-white.shadow.sm\\:rounded-lg.mb-6');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    </script>
</x-app-layout>

