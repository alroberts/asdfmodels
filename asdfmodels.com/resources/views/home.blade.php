<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASDF Models') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header -->
    <x-header />

    <!-- Hero Slideshow -->
    <x-hero-slideshow />

    <!-- Main Content -->
    <main class="w-full">
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
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t-2 border-black mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-700">
                <p>&copy; {{ date('Y') }} ASDF Models. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
