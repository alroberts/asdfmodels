<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.verification.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Review Verification') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                <h3 class="text-xl font-semibold text-black mb-4">User Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium text-black">{{ $verification->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium text-black">{{ $verification->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Verification Type</p>
                        <p class="font-medium text-black">{{ ucfirst(str_replace('_', ' ', $verification->verification_type)) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Submitted</p>
                        <p class="font-medium text-black">{{ $verification->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- ID Document -->
            @if($verification->id_document_path)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-semibold text-black mb-4">ID Document</h3>
                    @if(str_ends_with($verification->id_document_path, '.pdf'))
                        <iframe src="{{ asset($verification->id_document_path) }}" class="w-full h-96 border-2 border-black rounded"></iframe>
                    @else
                        <img src="{{ asset($verification->id_document_path) }}" alt="ID Document" class="max-w-full h-auto border-2 border-black rounded">
                    @endif
                </div>
            @endif

            <!-- Video -->
            @if($verification->video_path)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-semibold text-black mb-4">Video Identification</h3>
                    <video controls class="w-full border-2 border-black rounded">
                        <source src="{{ asset($verification->video_path) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.verification.approve', $verification->id) }}" class="inline-block mr-4">
                    @csrf
                    <x-primary-button class="bg-green-600 hover:bg-green-700">
                        Approve
                    </x-primary-button>
                </form>

                <form method="POST" action="{{ route('admin.verification.reject', $verification->id) }}" class="inline-block">
                    @csrf
                    <div class="mb-4">
                        <x-input-label for="rejection_reason" :value="__('Rejection Reason')" />
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" required></textarea>
                        <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
                    </div>
                    <x-primary-button class="bg-red-600 hover:bg-red-700">
                        Reject
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

