<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Details') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                <h3 class="text-xl font-semibold text-black mb-4">User Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium text-black">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium text-black">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Type</p>
                        <p class="font-medium text-black">
                            @if($user->is_photographer)
                                Photographer
                            @else
                                Model
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Joined</p>
                        <p class="font-medium text-black">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            @if($user->modelProfile)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-semibold text-black mb-4">Model Profile</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="font-medium text-black">{{ $user->modelProfile->location_city }}{{ $user->modelProfile->location_city && $user->modelProfile->location_country ? ', ' : '' }}{{ $user->modelProfile->location_country }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Verified</p>
                            <p class="font-medium text-black">{{ $user->modelProfile->isVerified() ? 'Yes' : 'No' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 inline-block mb-4">
                    Edit User
                </a>
                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

