<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="bg-white shadow sm:rounded-lg mb-6 overflow-hidden">
                <div class="bg-gray-100 h-48 md:h-64 relative">
                    @if($profile->cover_photo_path)
                        <img src="{{ asset($profile->cover_photo_path) }}" alt="Cover" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="p-6 md:p-8">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <div class="relative -mt-16 md:-mt-20">
                            @if($profile->profile_photo_path)
                                <img src="{{ asset($profile->profile_photo_path) }}" alt="{{ $user->name }}" class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white object-cover shadow-lg">
                            @else
                                <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white bg-gray-300 flex items-center justify-center shadow-lg">
                                    <span class="text-4xl text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            @if($profile->isVerified())
                                <div class="absolute bottom-0 right-0 bg-green-500 rounded-full p-2 border-4 border-white">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 mt-4 md:mt-0">
                            <h1 class="text-3xl font-bold text-black mb-2">{{ $user->name }}</h1>
                            @if($profile->location_city || $profile->location_country)
                                <p class="text-gray-600 mb-2">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $profile->location_city }}{{ $profile->location_city && $profile->location_country ? ', ' : '' }}{{ $profile->location_country }}
                                </p>
                            @endif
                            @if($profile->age)
                                <p class="text-gray-600 mb-4">{{ $profile->age }} years old</p>
                            @endif
                            @if($profile->bio)
                                <p class="text-gray-700">{{ $profile->bio }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats & Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-black mb-4">Physical Stats</h2>
                    <div class="space-y-2 text-gray-700">
                        @if($profile->height)
                            <p><strong>Height:</strong> {{ $profile->height }}</p>
                        @endif
                        @if($profile->weight)
                            <p><strong>Weight:</strong> {{ $profile->weight }}</p>
                        @endif
                        @if($profile->gender === 'male')
                            @if($profile->chest)<p><strong>Chest:</strong> {{ $profile->chest }}</p>@endif
                            @if($profile->waist)<p><strong>Waist:</strong> {{ $profile->waist }}</p>@endif
                            @if($profile->inseam)<p><strong>Inseam:</strong> {{ $profile->inseam }}</p>@endif
                            @if($profile->suit_size)<p><strong>Suit Size:</strong> {{ $profile->suit_size }}</p>@endif
                        @elseif($profile->gender === 'female')
                            @if($profile->bust)<p><strong>Bust:</strong> {{ $profile->bust }}</p>@endif
                            @if($profile->waist)<p><strong>Waist:</strong> {{ $profile->waist }}</p>@endif
                            @if($profile->hips)<p><strong>Hips:</strong> {{ $profile->hips }}</p>@endif
                            @if($profile->dress_size)<p><strong>Dress Size:</strong> {{ $profile->dress_size }}</p>@endif
                        @endif
                        @if($profile->shoe_size)<p><strong>Shoe Size:</strong> {{ $profile->shoe_size }}</p>@endif
                        @if($profile->hair_color)<p><strong>Hair:</strong> {{ $profile->hair_color }}</p>@endif
                        @if($profile->eye_color)<p><strong>Eyes:</strong> {{ $profile->eye_color }}</p>@endif
                    </div>
                </div>

                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-black mb-4">Contact</h2>
                    <div class="space-y-2">
                        @if($profile->public_email)
                            <p class="text-gray-700">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:{{ $profile->public_email }}" class="text-blue-600 hover:underline">{{ $profile->public_email }}</a>
                            </p>
                        @endif
                        @if($profile->instagram)
                            <p class="text-gray-700">
                                <i class="fab fa-instagram"></i>
                                <a href="https://instagram.com/{{ ltrim($profile->instagram, '@') }}" target="_blank" class="text-blue-600 hover:underline">{{ $profile->instagram }}</a>
                            </p>
                        @endif
                        @if($profile->portfolio_website)
                            <p class="text-gray-700">
                                <i class="fas fa-globe"></i>
                                <a href="{{ $profile->portfolio_website }}" target="_blank" class="text-blue-600 hover:underline">Portfolio Website</a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Polaroids Section -->
            @if($polaroids->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">Polaroids</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach($polaroids as $image)
                            <div class="aspect-square overflow-hidden rounded-lg">
                                <img src="{{ asset($image->thumbnail_path) }}" alt="Polaroid" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer" onclick="openLightbox('{{ asset($image->full_path) }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Featured Images -->
            @if($featuredImages->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">Featured Work</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($featuredImages as $image)
                            <div class="aspect-square overflow-hidden rounded-lg">
                                <img src="{{ asset($image->thumbnail_path) }}" alt="{{ $image->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer" onclick="openLightbox('{{ asset($image->full_path) }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center" onclick="closeLightbox()">
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300">&times;</button>
    </div>

    <script>
        function openLightbox(imageSrc) {
            document.getElementById('lightbox-image').src = imageSrc;
            document.getElementById('lightbox').classList.remove('hidden');
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
        }
    </script>
</x-app-layout>

