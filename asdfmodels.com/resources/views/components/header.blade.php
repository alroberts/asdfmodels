@php
    $user = auth()->user();
    if ($user) {
        // Check for admin first, then photographer, default to model
        $userType = 'model'; // Default
        if (isset($user->is_admin) && $user->is_admin) {
            $userType = 'admin';
        } elseif (isset($user->is_photographer) && $user->is_photographer) {
            $userType = 'photographer';
        }
    } else {
        $userType = 'guest';
    }
@endphp

<header class="bg-white border-b-2 border-black sticky top-0 z-50 shadow-sm" x-data="{ mobileMenuOpen: false }">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('assets/graphics/logo/ASDFModels.svg') }}" alt="ASDF Models" class="h-10 w-auto">
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-1">
                @if($userType === 'guest')
                    <a href="{{ route('home') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Home</a>
                    <a href="{{ route('login') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Login</a>
                    <a href="{{ route('register') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 text-sm font-medium transition">Register</a>
                @elseif($userType === 'model')
                    <a href="{{ route('dashboard') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">My Profile</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Portfolio</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Messages</a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition flex items-center">
                            <span>{{ $user->name }}</span>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white border-2 border-black rounded-md shadow-lg z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-black hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @elseif($userType === 'photographer')
                    <a href="{{ route('dashboard') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Browse Models</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">My Portfolio</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Messages</a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition flex items-center">
                            <span>{{ $user->name }}</span>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white border-2 border-black rounded-md shadow-lg z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-black hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @elseif($userType === 'admin')
                    <a href="{{ route('dashboard') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Users</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Content</a>
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Settings</a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition flex items-center">
                            <span>{{ $user->name }}</span>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white border-2 border-black rounded-md shadow-lg z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-black hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-black hover:bg-gray-100 p-2 rounded-md">
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t-2 border-black">
            <div class="px-2 pt-2 pb-3 space-y-1">
                @if($userType === 'guest')
                    <a href="{{ route('home') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Home</a>
                    <a href="{{ route('login') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Login</a>
                    <a href="{{ route('register') }}" class="block bg-black text-white px-3 py-2 rounded-md text-base font-medium">Register</a>
                @elseif($userType === 'model')
                    <a href="{{ route('dashboard') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">My Profile</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Portfolio</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Messages</a>
                    <a href="{{ route('profile.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                @elseif($userType === 'photographer')
                    <a href="{{ route('dashboard') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Browse Models</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">My Portfolio</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Messages</a>
                    <a href="{{ route('profile.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                @elseif($userType === 'admin')
                    <a href="{{ route('dashboard') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Users</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Content</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                @endif
            </div>
        </div>
    </nav>
</header>
