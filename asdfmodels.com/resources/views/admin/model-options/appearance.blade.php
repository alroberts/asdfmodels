<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Model Appearance Options') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded border-2 border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                <h3 class="text-lg font-semibold text-black mb-2">Appearance Lists</h3>
                <p class="text-sm text-gray-600 mb-6">Use one label per line. These options will appear in the model profile wizard.</p>

                <form method="POST" action="{{ route('admin.model-options.appearance.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="hair_colors" :value="__('Hair Colours')" />
                            <textarea id="hair_colors" name="hair_colors" rows="12" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">{{ old('hair_colors', implode("\n", $hairColors)) }}</textarea>
                            <x-input-error :messages="$errors->get('hair_colors')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="eye_colors" :value="__('Eye Colours')" />
                            <textarea id="eye_colors" name="eye_colors" rows="12" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">{{ old('eye_colors', implode("\n", $eyeColors)) }}</textarea>
                            <x-input-error :messages="$errors->get('eye_colors')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            Save Appearance Options
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
