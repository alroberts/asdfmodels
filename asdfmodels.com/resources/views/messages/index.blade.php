<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Messages') }}
            </h2>
            <a href="{{ route('messages.create') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                New Message
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if($threads->count() > 0)
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    @foreach($threads as $thread)
                        <a href="{{ route('messages.show', $thread->id) }}" class="block border-b-2 border-gray-200 hover:bg-gray-50 transition">
                            <div class="p-4 flex items-center space-x-4">
                                @if($thread->other_user->modelProfile && $thread->other_user->modelProfile->profile_photo_path)
                                    <img src="{{ asset($thread->other_user->modelProfile->profile_photo_path) }}" alt="{{ $thread->other_user->name }}" class="w-16 h-16 rounded-full object-cover">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-2xl text-gray-600">{{ substr($thread->other_user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-black">{{ $thread->other_user->name }}</h3>
                                        @if($thread->last_message_at)
                                            <span class="text-sm text-gray-500">{{ $thread->last_message_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    @if($thread->messages->count() > 0)
                                        <p class="text-sm text-gray-600 truncate mt-1">
                                            {{ Str::limit($thread->messages->first()->body, 100) }}
                                        </p>
                                    @endif
                                </div>
                                @if($thread->unread_count > 0)
                                    <span class="bg-black text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-semibold">
                                        {{ $thread->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-white shadow sm:rounded-lg p-8 text-center">
                    <p class="text-gray-600 mb-4">No messages yet.</p>
                    <a href="{{ route('messages.create') }}" class="inline-block bg-black text-white px-6 py-3 rounded hover:bg-gray-800">
                        Start a Conversation
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

