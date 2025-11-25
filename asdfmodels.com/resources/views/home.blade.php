<x-app-layout>
    <!-- Hero Slideshow -->
    <x-hero-slideshow />

    <!-- Main Content -->
    <div class="w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <!-- Main Content Grid -->
            <div class="grid md:grid-cols-2 gap-8 mb-16">
                <div class="bg-white border-2 border-black p-8 hover:shadow-xl transition-shadow">
                    <h2 class="text-3xl font-bold text-black mb-4">For Models</h2>
                    <p class="text-lg text-gray-700 mb-6">Create your profile, showcase your portfolio, and connect with photographers looking for talent.</p>
                    <ul class="list-disc list-inside text-gray-700 space-y-3 mb-6 text-lg">
                        <li>Build your professional portfolio</li>
                        <li>Get discovered by photographers</li>
                        <li>Access educational resources</li>
                        <li>Connect with industry professionals</li>
                    </ul>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block bg-black text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">Go to Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block bg-black text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">Join as Model</a>
                    @endauth
                </div>

                <div class="bg-white border-2 border-black p-8 hover:shadow-xl transition-shadow">
                    <h2 class="text-3xl font-bold text-black mb-4">For Photographers</h2>
                    <p class="text-lg text-gray-700 mb-6">Find models for your projects, showcase your work, and build your network.</p>
                    <ul class="list-disc list-inside text-gray-700 space-y-3 mb-6 text-lg">
                        <li>Search and discover models</li>
                        <li>Showcase your portfolio</li>
                        <li>Post casting calls</li>
                        <li>Build professional relationships</li>
                    </ul>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block bg-black text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">Go to Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block bg-black text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">Join as Photographer</a>
                    @endauth
                </div>
            </div>

            <!-- Featured Models Section -->
            @if($featuredModels->count() > 0)
                <div class="mb-16">
                    <h2 class="text-4xl font-bold text-black mb-8 text-center">Featured Models</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach($featuredModels as $model)
                            <a href="{{ route('models.show', $model->user_id) }}" class="bg-white border-2 border-black rounded-lg overflow-hidden hover:shadow-xl transition-shadow">
                                @if($model->profile_photo_path)
                                    <img src="{{ asset($model->profile_photo_path) }}" alt="{{ $model->user->name }}" class="w-full aspect-square object-cover">
                                @else
                                    <div class="w-full aspect-square bg-gray-200 flex items-center justify-center">
                                        <span class="text-4xl text-gray-600">{{ substr($model->user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="p-3 text-center">
                                    <h3 class="font-semibold text-black">{{ $model->user->name }}</h3>
                                    @if($model->isVerified())
                                        <span class="inline-block mt-1 bg-green-500 text-white text-xs px-2 py-1 rounded">
                                            <i class="fas fa-check"></i> Verified
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Newest Members Section -->
            @if($newestMembers->count() > 0)
                <div class="mb-16">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-4xl font-bold text-black">Newest Members</h2>
                        <a href="{{ route('models.browse') }}" class="text-black hover:underline font-semibold">View All →</a>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                        @foreach($newestMembers as $model)
                            <a href="{{ route('models.show', $model->user_id) }}" class="bg-white border-2 border-black rounded-lg overflow-hidden hover:shadow-xl transition-shadow">
                                @if($model->profile_photo_path)
                                    <img src="{{ asset($model->profile_photo_path) }}" alt="{{ $model->user->name }}" class="w-full aspect-square object-cover">
                                @else
                                    <div class="w-full aspect-square bg-gray-200 flex items-center justify-center">
                                        <span class="text-3xl text-gray-600">{{ substr($model->user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="p-2 text-center">
                                    <h3 class="font-semibold text-sm text-black truncate">{{ $model->user->name }}</h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Features Section -->
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-black mb-12">Platform Features</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-8 border-2 border-black hover:shadow-xl transition-shadow">
                        <div class="text-4xl mb-4">📸</div>
                        <h3 class="text-2xl font-bold text-black mb-4">Portfolio Galleries</h3>
                        <p class="text-gray-700 text-lg">Showcase your best work with professional photo galleries</p>
                    </div>
                    <div class="p-8 border-2 border-black hover:shadow-xl transition-shadow">
                        <div class="text-4xl mb-4">✓</div>
                        <h3 class="text-2xl font-bold text-black mb-4">Verified Profiles</h3>
                        <p class="text-gray-700 text-lg">Increase credibility with verified profile badges</p>
                    </div>
                    <div class="p-8 border-2 border-black hover:shadow-xl transition-shadow">
                        <div class="text-4xl mb-4">📚</div>
                        <h3 class="text-2xl font-bold text-black mb-4">Educational Resources</h3>
                        <p class="text-gray-700 text-lg">Access guides and articles to grow your career</p>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
