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
        $creditNotificationCount = \App\Models\PortfolioCredit::awaitingResponse($user, $user->is_photographer ? 'photographer' : 'model')->count();
        $otherNotificationCount = \App\Models\SiteNotification::where('user_id', $user->id)
            ->whereNotIn('type', ['credit_pending', 'message'])
            ->whereNull('read_at')
            ->count();
        $notificationCount = $creditNotificationCount + $otherNotificationCount;
        $messageUnreadCount = \App\Models\Message::whereHas('thread', function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
        $headerProfile = $userType === 'photographer'
            ? $user->photographerProfile
            : ($userType === 'model' ? $user->modelProfile : null);
        $headerDisplayName = $userType === 'admin'
            ? 'Admin'
            : ($headerProfile?->display_name ?: $user->display_name ?: $user->name);
        $headerAvatarPath = $headerProfile?->profile_photo_path;
        $headerUsername = $user->username;
        $headerInitialParts = preg_split('/\s+/', trim($headerDisplayName));
        $headerInitials = count($headerInitialParts) > 1
            ? strtoupper(substr($headerInitialParts[0], 0, 1) . substr(end($headerInitialParts), 0, 1))
            : strtoupper(substr($headerDisplayName, 0, 1));
    } else {
        $userType = 'guest';
        $notificationCount = 0;
        $messageUnreadCount = 0;
        $headerDisplayName = null;
        $headerAvatarPath = null;
        $headerUsername = null;
        $headerInitials = null;
    }
@endphp

<style>
    .site-user-trigger {
        align-items: center;
        border-radius: 999px;
        color: #050505;
        display: inline-flex;
        font-size: 14px;
        font-weight: 700;
        gap: 9px;
        padding: 6px 10px 6px 7px;
        transition: background-color 150ms ease;
    }

    .site-user-trigger:hover,
    .site-user-trigger.is-open {
        background: #f3f4f6;
    }

    .site-user-avatar {
        align-items: center;
        background: #111827;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 11px;
        font-weight: 850;
        height: 30px;
        justify-content: center;
        letter-spacing: .02em;
        overflow: hidden;
        width: 30px;
    }

    .site-user-avatar img {
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .site-user-menu-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
    }

    .site-user-menu-name {
        color: #050505;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.2;
    }

    .site-user-menu-username {
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        margin-top: 3px;
    }
</style>

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
                    <a href="{{ $user->hasCompletedModelProfile() ? route('models.show', $user->profileRouteIdentifier()) : route('profile.model.edit') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">My Profile</a>
                    <a href="{{ route('photographers.browse') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Find a Photographer</a>
                    <a href="{{ route('portfolio.index') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Portfolio</a>
                    <a href="{{ route('portfolio.galleries.index') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Galleries</a>
                    <x-message-launcher :count="$messageUnreadCount" />
                    <x-notification-bell :count="$notificationCount" />
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="site-user-trigger" :class="{ 'is-open': open }">
                            <span class="site-user-avatar">
                                @if($headerAvatarPath)
                                    <img src="{{ asset($headerAvatarPath) }}" alt="{{ $headerDisplayName }}">
                                @else
                                    {{ $headerInitials }}
                                @endif
                            </span>
                            <span>{{ $headerDisplayName }}</span>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border-2 border-black rounded-md shadow-lg z-50">
                            <div class="site-user-menu-header">
                                <p class="site-user-menu-name">{{ $headerDisplayName }}</p>
                                <p class="site-user-menu-username">{{ '@' . $headerUsername }}</p>
                            </div>
                            <a href="{{ route('profile.model.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Edit Profile</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-black hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @elseif($userType === 'photographer')
                    <a href="{{ route('dashboard') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
                    <a href="{{ $user->photographerProfile ? route('photographers.show', $user->profileRouteIdentifier()) : route('photographers.profile.edit') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">My Profile</a>
                    <a href="{{ route('models.browse') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Find a Model</a>
                    <a href="{{ route('portfolio.index') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">My Portfolio</a>
                    <x-message-launcher :count="$messageUnreadCount" />
                    <x-notification-bell :count="$notificationCount" />
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="site-user-trigger" :class="{ 'is-open': open }">
                            <span class="site-user-avatar">
                                @if($headerAvatarPath)
                                    <img src="{{ asset($headerAvatarPath) }}" alt="{{ $headerDisplayName }}">
                                @else
                                    {{ $headerInitials }}
                                @endif
                            </span>
                            <span>{{ $headerDisplayName }}</span>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border-2 border-black rounded-md shadow-lg z-50">
                            <div class="site-user-menu-header">
                                <p class="site-user-menu-name">{{ $headerDisplayName }}</p>
                                <p class="site-user-menu-username">{{ '@' . $headerUsername }}</p>
                            </div>
                            <a href="{{ route('photographers.profile.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Edit Profile</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-black hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @elseif($userType === 'admin')
                    <a href="{{ route('models.browse') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Find a Model</a>
                    <a href="{{ route('photographers.browse') }}" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Find a Photographer</a>
                    <x-notification-bell :count="$notificationCount" />
                    <a href="#" class="text-black hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-medium transition">Support</a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="site-user-trigger" :class="{ 'is-open': open }">
                            <span class="site-user-avatar">{{ $headerInitials }}</span>
                            <span>{{ $headerDisplayName }}</span>
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white border-2 border-black rounded-md shadow-lg z-50">
                            <div class="site-user-menu-header">
                                <p class="site-user-menu-name">{{ $headerDisplayName }}</p>
                                <p class="site-user-menu-username">{{ '@' . $headerUsername }}</p>
                            </div>
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Dashboard</a>
                            <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Users</a>
                            <a href="{{ route('admin.verification.index') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Verifications</a>
                            <a href="{{ url('/admin/photographer-options/specialties') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Specialties</a>
                            <a href="{{ url('/admin/photographer-options/services') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Services</a>
                            <a href="{{ url('/admin/model-options/appearance') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Model Attributes</a>
                            <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Settings</a>
                            <div class="border-t-2 border-gray-200 my-1"></div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-black hover:bg-gray-100">Account Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-black hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Mobile Actions -->
            <div class="md:hidden flex items-center gap-1">
                @auth
                    <x-message-launcher :count="$messageUnreadCount" />
                    <x-notification-bell :count="$notificationCount" />
                @endauth
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
                    <a href="{{ $user->hasCompletedModelProfile() ? route('models.show', $user->profileRouteIdentifier()) : route('profile.model.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">My Profile</a>
                    <a href="{{ route('profile.model.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Edit Profile</a>
                    <a href="{{ route('photographers.browse') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Find a Photographer</a>
                    <a href="{{ route('portfolio.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Portfolio</a>
                    <a href="{{ route('portfolio.galleries.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Galleries</a>
                    <a href="{{ route('notifications.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Notifications{{ $notificationCount > 0 ? ' (' . $notificationCount . ')' : '' }}</a>
                    <a href="{{ route('profile.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                @elseif($userType === 'photographer')
                    <a href="{{ route('dashboard') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    <a href="{{ $user->photographerProfile ? route('photographers.show', $user->profileRouteIdentifier()) : route('photographers.profile.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">My Profile</a>
                    <a href="{{ route('models.browse') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Find a Model</a>
                    <a href="{{ route('portfolio.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">My Portfolio</a>
                    <a href="{{ route('notifications.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Notifications{{ $notificationCount > 0 ? ' (' . $notificationCount . ')' : '' }}</a>
                    <a href="{{ route('photographers.profile.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Edit Profile</a>
                    <a href="{{ route('profile.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                @elseif($userType === 'admin')
                    <a href="{{ route('models.browse') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Find a Model</a>
                    <a href="{{ route('photographers.browse') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Find a Photographer</a>
                    <a href="{{ route('notifications.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Notifications{{ $notificationCount > 0 ? ' (' . $notificationCount . ')' : '' }}</a>
                    <a href="#" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Support</a>
                    <div class="border-t-2 border-gray-200 my-2"></div>
                    <p class="px-3 py-2 text-sm font-semibold text-gray-500 uppercase">Admin</p>
                    <a href="{{ route('admin.dashboard') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    <a href="{{ route('admin.users.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Users</a>
                    <a href="{{ route('admin.verification.index') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Verifications</a>
                    <a href="{{ url('/admin/photographer-options/specialties') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Specialties</a>
                    <a href="{{ url('/admin/photographer-options/services') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Services</a>
                    <a href="{{ url('/admin/model-options/appearance') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Model Attributes</a>
                    <a href="{{ route('admin.settings') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Settings</a>
                    <div class="border-t-2 border-gray-200 my-2"></div>
                    <a href="{{ route('profile.edit') }}" class="block text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Account Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-black hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                @endif
            </div>
        </div>
    </nav>
</header>
