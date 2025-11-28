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
                            @if($profile->experience_level)
                                <p class="text-gray-600 mb-4 capitalize">{{ $profile->experience_level }} Photographer</p>
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
                    <h2 class="text-xl font-semibold text-black mb-4">Professional Information</h2>
                    <div class="space-y-3 text-gray-700">
                        @php
                            $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties();
                            $servicesOptions = \App\Helpers\PhotographerOptions::services();
                            
                            // Filter out specialties that no longer exist in the database
                            $validSpecialties = $profile->specialties ? array_intersect_key(
                                array_flip($profile->specialties),
                                $specialtiesOptions
                            ) : [];
                        @endphp
                        
                        @if(!empty($validSpecialties))
                            <div>
                                <strong>Specialties:</strong>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach(array_keys($validSpecialties) as $specialty)
                                        <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">
                                            {{ $specialtiesOptions[$specialty] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @php
                            $equipment = $profile->equipment ?? [];
                            $hasEquipment = false;
                            if (is_array($equipment)) {
                                if (isset($equipment['cameras']) || isset($equipment['lenses']) || isset($equipment['lighting']) || isset($equipment['other'])) {
                                    // New structured format
                                    $hasEquipment = !empty($equipment['cameras']) || !empty($equipment['lenses']) || !empty($equipment['lighting']) || !empty($equipment['other']);
                                } else {
                                    // Old format - simple array
                                    $hasEquipment = count($equipment) > 0;
                                }
                            }
                        @endphp
                        
                        @if($hasEquipment)
                            <div>
                                <strong>Equipment:</strong>
                                <div class="space-y-2 mt-1">
                                    @if(isset($equipment['cameras']) && count($equipment['cameras']) > 0)
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Cameras:</span>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach($equipment['cameras'] as $item)
                                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">{{ $item }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($equipment['lenses']) && count($equipment['lenses']) > 0)
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Lenses:</span>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach($equipment['lenses'] as $item)
                                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">{{ $item }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($equipment['lighting']) && count($equipment['lighting']) > 0)
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Lighting:</span>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach($equipment['lighting'] as $item)
                                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">{{ $item }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($equipment['other']) && count($equipment['other']) > 0)
                                        <div>
                                            <span class="text-sm font-medium text-gray-600">Other:</span>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach($equipment['other'] as $item)
                                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">{{ $item }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(!isset($equipment['cameras']) && is_array($equipment) && count($equipment) > 0)
                                        {{-- Old format --}}
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($equipment as $item)
                                                <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">{{ $item }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        @php
                            // Filter out services that no longer exist in the database
                            $validServices = $profile->services_offered ? array_intersect_key(
                                array_flip($profile->services_offered),
                                $servicesOptions
                            ) : [];
                        @endphp
                        
                        @if(!empty($validServices))
                            <div>
                                <strong>Services:</strong>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach(array_keys($validServices) as $service)
                                        <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">
                                            {{ $servicesOptions[$service] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($profile->studio_location)
                            <p><strong>Studio:</strong> {{ $profile->studio_location }}</p>
                        @endif
                        @if($profile->available_for_travel)
                            <p><strong>Available for travel:</strong> <i class="fas fa-check text-green-600"></i></p>
                        @endif
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
                        @if($profile->phone)
                            <p class="text-gray-700">
                                <i class="fas fa-phone"></i>
                                <a href="tel:{{ $profile->phone }}" class="text-blue-600 hover:underline">{{ $profile->phone }}</a>
                            </p>
                        @endif
                        @if($profile->instagram)
                            <p class="text-gray-700">
                                <i class="fab fa-instagram"></i>
                                <a href="https://instagram.com/{{ ltrim($profile->instagram, '@') }}" target="_blank" class="text-blue-600 hover:underline">{{ $profile->instagram }}</a>
                            </p>
                        @endif
                        @if($profile->facebook)
                            <p class="text-gray-700">
                                <i class="fab fa-facebook"></i>
                                <a href="https://facebook.com/{{ $profile->facebook }}" target="_blank" class="text-blue-600 hover:underline">{{ $profile->facebook }}</a>
                            </p>
                        @endif
                        @if($profile->twitter)
                            <p class="text-gray-700">
                                <i class="fab fa-twitter"></i>
                                <a href="https://twitter.com/{{ ltrim($profile->twitter, '@') }}" target="_blank" class="text-blue-600 hover:underline">{{ $profile->twitter }}</a>
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

            <!-- Featured Images -->
            @if($featuredImages->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">Featured Work</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($featuredImages as $image)
                            <div class="aspect-square overflow-hidden rounded-lg">
                                <img src="{{ asset($image->thumbnail_path) }}" alt="{{ $image->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer" onclick="openLightbox('{{ asset($image->full_path) }}')">
                                @if($image->model)
                                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 text-xs">
                                        <p class="truncate">{{ $image->model->name }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Portfolio Gallery -->
            @if($portfolioImages->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">Portfolio</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($portfolioImages as $image)
                            <div class="aspect-square overflow-hidden rounded-lg relative">
                                <img src="{{ asset($image->thumbnail_path) }}" alt="{{ $image->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer" onclick="openLightbox('{{ asset($image->full_path) }}')">
                                @if($image->model)
                                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 text-xs">
                                        <p class="truncate">{{ $image->model->name }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $portfolioImages->links() }}
                    </div>
                </div>
            @endif

            <!-- Tagged Images (Work with Models) -->
            @php
                $taggedImages = \App\Models\PortfolioImage::whereHas('photographerTags', function($query) use ($user) {
                    $query->where('photographer_id', $user->id);
                })
                ->where('is_public', true)
                ->with('model')
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            @endphp

            @if($taggedImages->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">Work with Models</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($taggedImages as $image)
                            <div class="relative group aspect-square overflow-hidden rounded-lg">
                                <a href="{{ route('models.show', $image->model_id) }}">
                                    <img src="{{ asset($image->thumbnail_path) }}" alt="Image" class="w-full h-full object-cover hover:scale-105 transition-transform">
                                </a>
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 text-xs">
                                    <p class="truncate">{{ $image->model->name }}</p>
                                </div>
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
