<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                {{ $dashboard['title'] }}
            </h2>
            <p class="text-sm text-gray-600">
                {{ $dashboard['intro'] }}
            </p>
        </div>
    </x-slot>

    <div class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 overflow-hidden shadow-sm sm:rounded-2xl">
                <div class="px-6 py-8 md:px-8 md:py-10 text-white">
                    <p class="text-sm uppercase tracking-[0.25em] text-gray-300">Welcome back</p>
                    <h3 class="mt-3 text-3xl font-semibold">{{ auth()->user()->name }}</h3>
                    <p class="mt-3 max-w-3xl text-sm md:text-base text-gray-200">
                        Use this page as your working home base for the platform. The next steps, profile links, and core account shortcuts all live here.
                    </p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach($dashboard['stats'] as $stat)
                    <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-2xl">
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                            <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-2xl">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    <p class="mt-1 text-sm text-gray-600">Jump straight into the areas you’re most likely to use.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($dashboard['quickLinks'] as $link)
                        <a href="{{ $link['route'] }}" class="group rounded-2xl border border-gray-200 bg-gray-50 p-5 transition hover:border-gray-400 hover:bg-white hover:shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900 group-hover:text-black">
                                        {{ $link['label'] }}
                                    </h4>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">
                                        {{ $link['description'] }}
                                    </p>
                                </div>
                                <span class="mt-1 text-gray-400 group-hover:text-gray-700">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
