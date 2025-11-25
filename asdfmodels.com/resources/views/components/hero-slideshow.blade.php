@php
    $images = [
        asset('assets/images/photo-shoot-1.jpg'),
        asset('assets/images/photo-shoot-2.jpg'),
        asset('assets/images/photo-shoot-3.jpg'),
        asset('assets/images/photo-shoot-4.jpg'),
        asset('assets/images/photo-shoot-5.jpg'),
        asset('assets/images/models-2.jpg'),
    ];
@endphp

<div class="relative h-[600px] md:h-[700px] lg:h-[800px] overflow-hidden" x-data="{
    currentSlide: 0,
    images: @json($images),
    init() {
        setInterval(() => {
            this.currentSlide = (this.currentSlide + 1) % this.images.length;
        }, 5000);
    },
    goToSlide(index) {
        this.currentSlide = index;
    }
}">
    <!-- Slides -->
    <div class="relative h-full">
        <template x-for="(image, index) in images" :key="index">
            <div 
                x-show="currentSlide === index"
                x-transition:enter="transition ease-out duration-1000"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-1000"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0"
            >
                <img 
                    :src="image" 
                    :alt="'Slide ' + (index + 1)"
                    class="w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-transparent"></div>
            </div>
        </template>
    </div>

    <!-- Overlay Content -->
    <div class="absolute inset-0 flex items-center justify-center z-10">
        <div class="text-center text-white px-4 max-w-4xl">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 drop-shadow-2xl">
                ASDF Models
            </h1>
            <p class="text-xl md:text-2xl lg:text-3xl mb-8 drop-shadow-lg">
                Where Talent Meets Opportunity
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-white text-black px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-white text-black px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                        Join Now
                    </a>
                    <a href="{{ route('login') }}" class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white/10 transition">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Slide Indicators -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 flex space-x-2">
        <template x-for="(image, index) in images" :key="index">
            <button
                @click="goToSlide(index)"
                :class="currentSlide === index ? 'bg-white' : 'bg-white/50'"
                class="w-3 h-3 rounded-full transition-all duration-300 hover:bg-white"
                :aria-label="'Go to slide ' + (index + 1)"
            ></button>
        </template>
    </div>

    <!-- Navigation Arrows -->
    <button
        @click="currentSlide = (currentSlide - 1 + images.length) % images.length"
        class="absolute left-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white p-3 rounded-full transition backdrop-blur-sm"
        aria-label="Previous slide"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    <button
        @click="currentSlide = (currentSlide + 1) % images.length"
        class="absolute right-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 text-white p-3 rounded-full transition backdrop-blur-sm"
        aria-label="Next slide"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
</div>

