<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Total Users</h3>
                    <p class="text-3xl font-bold text-black">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Models</h3>
                    <p class="text-3xl font-bold text-black">{{ $stats['total_models'] }}</p>
                </div>
                <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Photographers</h3>
                    <p class="text-3xl font-bold text-black">{{ $stats['total_photographers'] }}</p>
                </div>
                <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Verified Models</h3>
                    <p class="text-3xl font-bold text-black">{{ $stats['verified_models'] }}</p>
                </div>
                <div class="bg-white shadow sm:rounded-lg p-6 border-2 border-black">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Pending Verifications</h3>
                    <p class="text-3xl font-bold text-black">
                        <a href="{{ route('admin.verification.index') }}" class="hover:underline">{{ $stats['pending_verifications'] }}</a>
                    </p>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-black mb-4">Recent Users</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentUsers as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($user->is_photographer)
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Photographer</span>
                                        @else
                                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">Model</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

