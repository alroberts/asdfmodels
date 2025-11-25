<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Get Verified') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if($verification && $verification->status === 'pending')
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-yellow-800 mb-2">Verification Request Pending</h3>
                    <p class="text-yellow-700">Your verification request is currently under review. You will be notified once an admin has reviewed it.</p>
                    <p class="text-sm text-yellow-600 mt-2">Submitted: {{ $verification->created_at->format('M d, Y g:i A') }}</p>
                </div>
            @elseif($verification && $verification->status === 'rejected')
                <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-red-800 mb-2">Verification Request Rejected</h3>
                    @if($verification->rejection_reason)
                        <p class="text-red-700 mb-2"><strong>Reason:</strong> {{ $verification->rejection_reason }}</p>
                    @endif
                    <p class="text-red-700">You can submit a new verification request below.</p>
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-black mb-4">Submit Verification</h3>
                <p class="text-gray-700 mb-6">
                    To get verified, please submit either an ID document or a video identification. This helps us ensure the authenticity of profiles on our platform.
                </p>

                <form method="POST" action="{{ route('verification.store') }}" enctype="multipart/form-data" x-data="{ verificationType: 'id_upload' }">
                    @csrf

                    <div class="mb-6">
                        <x-input-label for="verification_type" :value="__('Verification Method')" />
                        <select id="verification_type" name="verification_type" x-model="verificationType" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" required>
                            <option value="id_upload">ID Document Upload</option>
                            <option value="video_identification">Video Identification</option>
                        </select>
                        <x-input-error :messages="$errors->get('verification_type')" class="mt-2" />
                    </div>

                    <div x-show="verificationType === 'id_upload'" class="mb-6">
                        <x-input-label for="id_document" :value="__('ID Document (PDF, JPG, PNG)')" />
                        <input type="file" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png" class="block mt-1 w-full border-2 border-black rounded-md" :required="verificationType === 'id_upload'">
                        <p class="mt-2 text-sm text-gray-600">Maximum file size: 10MB</p>
                        <x-input-error :messages="$errors->get('id_document')" class="mt-2" />
                    </div>

                    <div x-show="verificationType === 'video_identification'" class="mb-6">
                        <x-input-label for="video" :value="__('Video Identification (MP4, MOV, AVI)')" />
                        <input type="file" id="video" name="video" accept=".mp4,.mov,.avi" class="block mt-1 w-full border-2 border-black rounded-md" :required="verificationType === 'video_identification'">
                        <p class="mt-2 text-sm text-gray-600">Maximum file size: 50MB</p>
                        <x-input-error :messages="$errors->get('video')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>
                            {{ __('Submit Verification') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

