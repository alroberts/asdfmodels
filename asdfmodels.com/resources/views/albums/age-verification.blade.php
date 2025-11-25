<x-guest-layout>
    <div class="w-full sm:max-w-md mt-6 px-6 py-4">
        <div class="bg-white border-2 border-black p-8 rounded-lg">
            <h2 class="text-2xl font-bold text-black mb-4">Age Verification Required</h2>
            <p class="text-gray-700 mb-6">
                This album contains nudity and is restricted to viewers 18 years and older.
            </p>
            <p class="text-gray-600 text-sm mb-6">
                To view this content, please verify that you are 18 years or older.
            </p>
            
            <form method="POST" action="{{ route('albums.verify-age', $album->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="age_verified" value="1" required class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">I confirm that I am 18 years of age or older</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
                    <x-primary-button>
                        {{ __('Continue') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>

