<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-white">
        <div class="w-full max-w-4xl mx-auto px-6 py-12">
            <!-- Logo -->
            <div class="text-center mb-12">
                <img src="{{ asset('assets/graphics/logo/ASDFModels.svg') }}" alt="ASDF Models" class="h-16 mx-auto mb-8">
                <h1 class="text-4xl font-bold text-black mb-4">ASDF Models</h1>
                <p class="text-xl text-gray-700">Connecting models and photographers worldwide</p>
            </div>

            <!-- Main Content -->
            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <div class="bg-white border-2 border-black p-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">For Models</h2>
                    <p class="text-gray-700 mb-4">Create your profile, showcase your portfolio, and connect with photographers looking for talent.</p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                        <li>Build your professional portfolio</li>
                        <li>Get discovered by photographers</li>
                        <li>Access educational resources</li>
                        <li>Connect with industry professionals</li>
                    </ul>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition">Go to Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition">Join as Model</a>
                    @endauth
                </div>

                <div class="bg-white border-2 border-black p-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">For Photographers</h2>
                    <p class="text-gray-700 mb-4">Find models for your projects, showcase your work, and build your network.</p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                        <li>Search and discover models</li>
                        <li>Showcase your portfolio</li>
                        <li>Post casting calls</li>
                        <li>Build professional relationships</li>
                    </ul>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition">Go to Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition">Join as Photographer</a>
                    @endauth
                </div>
            </div>

            <!-- Features -->
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-black mb-8">Platform Features</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="p-4">
                        <h3 class="text-xl font-semibold text-black mb-2">Portfolio Galleries</h3>
                        <p class="text-gray-700">Showcase your best work with professional photo galleries</p>
                    </div>
                    <div class="p-4">
                        <h3 class="text-xl font-semibold text-black mb-2">Verified Profiles</h3>
                        <p class="text-gray-700">Increase credibility with verified profile badges</p>
                    </div>
                    <div class="p-4">
                        <h3 class="text-xl font-semibold text-black mb-2">Educational Resources</h3>
                        <p class="text-gray-700">Access guides and articles to grow your career</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="text-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-block bg-black text-white px-8 py-3 rounded text-lg font-semibold hover:bg-gray-800 transition mr-4">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-block border-2 border-black text-black px-8 py-3 rounded text-lg font-semibold hover:bg-gray-100 transition">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-block border-2 border-black text-black px-8 py-3 rounded text-lg font-semibold hover:bg-gray-100 transition mr-4">Login</a>
                    <a href="{{ route('register') }}" class="inline-block bg-black text-white px-8 py-3 rounded text-lg font-semibold hover:bg-gray-800 transition">Register</a>
                @endauth
            </div>
        </div>
    </div>
</x-guest-layout>

