<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verification Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Pending Verifications -->
            <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                <h3 class="text-xl font-semibold text-black mb-4">Pending Verifications ({{ $pending->count() }})</h3>
                @if($pending->count() > 0)
                    <div class="space-y-4">
                        @foreach($pending as $verification)
                            <div class="border-2 border-black p-4 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-black">{{ $verification->user->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $verification->user->email }}</p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Type: {{ ucfirst(str_replace('_', ' ', $verification->verification_type)) }} | 
                                            Submitted: {{ $verification->created_at->format('M d, Y g:i A') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('admin.verification.show', $verification->id) }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                                        Review
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">No pending verifications.</p>
                @endif
            </div>

            <!-- Recent Approved/Rejected -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-black mb-4">Recently Approved</h3>
                    @if($approved->count() > 0)
                        <div class="space-y-2">
                            @foreach($approved as $verification)
                                <div class="border border-gray-200 p-3 rounded">
                                    <p class="font-medium text-black">{{ $verification->user->name }}</p>
                                    <p class="text-sm text-gray-600">Approved by {{ $verification->reviewer->name ?? 'Admin' }}</p>
                                    <p class="text-xs text-gray-500">{{ $verification->reviewed_at->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 text-sm">No approved verifications yet.</p>
                    @endif
                </div>

                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-black mb-4">Recently Rejected</h3>
                    @if($rejected->count() > 0)
                        <div class="space-y-2">
                            @foreach($rejected as $verification)
                                <div class="border border-gray-200 p-3 rounded">
                                    <p class="font-medium text-black">{{ $verification->user->name }}</p>
                                    <p class="text-sm text-gray-600">Rejected by {{ $verification->reviewer->name ?? 'Admin' }}</p>
                                    <p class="text-xs text-gray-500">{{ $verification->reviewed_at->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 text-sm">No rejected verifications yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

