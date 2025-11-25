<footer class="bg-white border-t-2 border-black mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Logo and Description -->
            <div>
                <a href="{{ route('home') }}" class="flex items-center mb-4">
                    <img src="{{ asset('assets/graphics/logo/ASDFModels.svg') }}" alt="ASDF Models" class="h-8 w-auto">
                </a>
                <p class="text-gray-600 text-sm">Connecting models and photographers worldwide.</p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="font-semibold text-black mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('models.browse') }}" class="text-gray-600 hover:text-black text-sm transition">Browse Models</a></li>
                    <li><a href="{{ route('photographers.browse') }}" class="text-gray-600 hover:text-black text-sm transition">Browse Photographers</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-black text-sm transition">Support</a></li>
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-black text-sm transition">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-gray-600 hover:text-black text-sm transition">Login</a></li>
                        <li><a href="{{ route('register') }}" class="text-gray-600 hover:text-black text-sm transition">Register</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Contact / Legal -->
            <div>
                <h3 class="font-semibold text-black mb-4">Legal</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('legal.terms') }}" class="text-gray-600 hover:text-black text-sm transition">Terms of Service</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="text-gray-600 hover:text-black text-sm transition">Privacy Policy</a></li>
                    <li><a href="{{ route('legal.cookies') }}" class="text-gray-600 hover:text-black text-sm transition">Cookie Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t-2 border-gray-200 mt-8 pt-6">
            <p class="text-center text-gray-600 text-sm">
                &copy; {{ date('Y') }} ASDF Models. All rights reserved.
            </p>
        </div>
    </div>
</footer>

