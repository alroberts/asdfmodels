@php
    $countries = $countries ?? config('countries');
    $specialtiesOptions = $specialtiesOptions ?? \App\Helpers\PhotographerOptions::specialties('photographer');
    $servicesOptions = $servicesOptions ?? \App\Helpers\PhotographerOptions::services();

    $nameParts = preg_split('/\s+/', trim((string) $user->getRawOriginal('name'))) ?: [];
    $initialFirstName = old('first_name', $user->first_name ?: ($nameParts[0] ?? ''));
    $initialLastName = old('last_name', $user->last_name ?: (count($nameParts) > 1 ? end($nameParts) : ''));
    $companyName = old('professional_name', $profile->professional_name ?? '');
    $isVerifiedProfile = $profile->isVerified();
    $canEditUsername = $profile->isVerified();
    $initialUsername = old('username', $user->username ?? '');
    $hasChangedUsernameBefore = $user->hasChangedUsernameBefore();

    $firstInitial = $initialFirstName !== '' ? mb_substr($initialFirstName, 0, 1) . '.' : '';
    $lastInitial = $initialLastName !== '' ? mb_substr($initialLastName, 0, 1) . '.' : '';
    $fullName = trim($initialFirstName . ' ' . $initialLastName);
    $initialDisplayNameFormat = old('display_name_format', $profile->display_name_format ?: 'first_name_last_initial');
    if (!$isVerifiedProfile && in_array($initialDisplayNameFormat, ['professional_name', 'full_name'], true)) {
        $initialDisplayNameFormat = 'first_name_last_initial';
    }

    $displayNameFormatOptions = [
        [
            'value' => 'professional_name',
            'label' => $companyName ? 'Company: ' . $companyName : 'Company / professional name',
            'description' => 'Show your studio or trading name as the primary profile name.',
            'locked' => !$isVerifiedProfile,
        ],
        [
            'value' => 'full_name',
            'label' => $fullName ?: 'Full name',
            'description' => 'Show your full first and last name.',
            'locked' => !$isVerifiedProfile,
        ],
        [
            'value' => 'first_name_last_initial',
            'label' => trim($initialFirstName . ' ' . $lastInitial) ?: 'First name + last initial',
            'description' => 'Show your first name with a redacted last name.',
        ],
        [
            'value' => 'first_name',
            'label' => $initialFirstName ?: 'First name only',
            'description' => 'Show only your first name.',
        ],
        [
            'value' => 'initials',
            'label' => trim($firstInitial . $lastInitial) ?: 'Initials',
            'description' => 'Show only initials.',
        ],
    ];

    $selectedSpecialties = old('specialties', $profile->specialties ?? []);
    $selectedServices = old('services_offered', $profile->services_offered ?? []);
    $equipment = old('equipment', $profile->equipment ?? []);
    $equipment = is_array($equipment) ? $equipment : [];
    $equipmentText = fn ($key) => implode("\n", array_filter($equipment[$key] ?? []));
    $locationCountryCode = old('location_country_code', $profile->location_country_code ?? '');
    $socialPlatformOptions = [
        ['value' => 'instagram', 'label' => 'Instagram'],
        ['value' => 'facebook', 'label' => 'Facebook'],
        ['value' => 'x', 'label' => 'X'],
        ['value' => 'tiktok', 'label' => 'TikTok'],
        ['value' => 'youtube', 'label' => 'YouTube'],
        ['value' => 'behance', 'label' => 'Behance'],
        ['value' => 'linkedin', 'label' => 'LinkedIn'],
        ['value' => 'website', 'label' => 'Website / Portfolio'],
    ];
    $initialSocialLinks = collect(old('social_links', $profile->social_links ?? []))
        ->filter(fn ($link) => filled($link['platform'] ?? null) || filled($link['url'] ?? null))
        ->values()
        ->map(fn ($link) => [
            'platform' => $link['platform'] ?? '',
            'url' => $link['url'] ?? '',
            'uid' => uniqid('social_', true),
        ])
        ->all();

    if ($initialSocialLinks === []) {
        foreach ([
            ['platform' => 'instagram', 'value' => old('instagram', $profile->instagram)],
            ['platform' => 'facebook', 'value' => old('facebook', $profile->facebook)],
            ['platform' => 'x', 'value' => old('twitter', $profile->twitter)],
            ['platform' => 'website', 'value' => old('portfolio_website', $profile->portfolio_website)],
        ] as $legacyLink) {
            if (filled($legacyLink['value'])) {
                $initialSocialLinks[] = [
                    'platform' => $legacyLink['platform'],
                    'url' => $legacyLink['value'],
                    'uid' => uniqid('social_', true),
                ];
            }
        }
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-gray-500">Photographer Profile</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-gray-900">Edit Your Profile</h2>
                <p class="mt-2 max-w-2xl text-sm text-gray-600">
                    Update your public details, professional services, equipment, and contact preferences in one place.
                </p>
            </div>
            <a href="{{ route('photographers.show', $user->profileRouteIdentifier()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                View Profile
            </a>
        </div>
    </x-slot>

    <div class="py-10" x-data="photographerProfileEditor()" x-init="init()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                x-show="toast.show"
                x-cloak
                x-transition:enter="transform ease-out duration-300"
                x-transition:enter-start="translate-y-2 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transform ease-in duration-200"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-2 opacity-0"
                class="fixed right-6 top-24 z-[70] max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg"
                :class="toast.type === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'"
            >
                <div class="flex items-start gap-3">
                    <i class="fas mt-0.5" :class="toast.type === 'error' ? 'fa-exclamation-circle text-red-600' : 'fa-check-circle text-green-600'"></i>
                    <p class="flex-1 font-medium" x-text="toast.message"></p>
                    <button type="button" @click="toast.show = false" class="transition hover:text-black">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6 md:hidden">
                <div class="overflow-x-auto">
                    <nav class="flex min-w-max gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm">
                        <a href="#section-basics" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Basics</a>
                        <a href="#section-professional" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Professional</a>
                        <a href="#section-equipment" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Equipment</a>
                        <a href="#section-contact" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Contact</a>
                        <a href="#section-visibility" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Visibility</a>
                    </nav>
                </div>
            </div>

            <div class="md:flex md:items-start md:gap-8">
                <aside class="hidden md:block md:w-1/4 md:max-w-xs md:flex-none md:self-start md:sticky md:top-24">
                    <div class="max-h-[calc(100vh-7rem)] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-base font-semibold text-gray-900">Profile Editor</h3>
                            <p class="mt-1 text-sm text-gray-500">Jump between profile areas while you edit.</p>
                        </div>

                        <nav class="space-y-1 text-sm">
                            <a href="#section-basics" class="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 hover:text-black">
                                <i class="fas fa-id-card w-4 text-center text-gray-400"></i>
                                <span>Basic Information</span>
                            </a>
                            <a href="#section-professional" class="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 hover:text-black">
                                <i class="fas fa-briefcase w-4 text-center text-gray-400"></i>
                                <span>Professional Details</span>
                            </a>
                            <a href="#section-equipment" class="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 hover:text-black">
                                <i class="fas fa-camera w-4 text-center text-gray-400"></i>
                                <span>Equipment</span>
                            </a>
                            <a href="#section-contact" class="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 hover:text-black">
                                <i class="fas fa-envelope w-4 text-center text-gray-400"></i>
                                <span>Contact & Links</span>
                            </a>
                            <a href="#section-visibility" class="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 hover:text-black">
                                <i class="fas fa-eye w-4 text-center text-gray-400"></i>
                                <span>Visibility</span>
                            </a>
                        </nav>

                        <div class="mt-6 border-t border-gray-200 pt-5">
                            @if(!$profile->isVerified())
                                <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                                    <p class="text-sm font-medium text-yellow-900">Verification Recommended</p>
                                    <p class="mt-1 text-xs leading-5 text-yellow-800">
                                        Verification unlocks company-name display and full-name display options.
                                    </p>
                                    <a href="{{ route('verification.create') }}" class="mt-3 inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-yellow-700">
                                        Submit Verification
                                    </a>
                                </div>
                            @else
                                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                                    Your profile is verified.
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>

                <form method="POST" action="{{ route('photographers.profile.update') }}" enctype="multipart/form-data" class="min-w-0 md:w-3/4 md:flex-1" @submit.prevent="submitProfile($event)">
                    @csrf
                    @method('patch')

                    <div>
                        <section id="section-basics">
                            <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                                <div class="mb-6">
                                    <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                        <i class="fas fa-id-card text-gray-400"></i>
                                        <span>Basic Information</span>
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">The main public details that set the tone for your photographer profile.</p>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="first_name" value="First Name" />
                                        <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full border-gray-300" value="{{ $initialFirstName }}" required />
                                        <p class="mt-2 text-xs text-gray-500">Used for safe public name formats and account communication.</p>
                                    </div>
                                    <div>
                                        <x-input-label for="last_name" value="Last Name" />
                                        <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full border-gray-300" value="{{ $initialLastName }}" required />
                                        <p class="mt-2 text-xs text-gray-500">Stored separately so it can be hidden from public display.</p>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <x-input-label for="username" value="Username" />
                                    <div class="mt-1 max-w-xl rounded-2xl border border-gray-200 bg-white p-2 shadow-sm transition focus-within:border-black focus-within:ring-1 focus-within:ring-black">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-10 items-center rounded-xl bg-gray-100 px-3 text-sm font-bold text-gray-500">@</span>
                                            <input
                                                id="username"
                                                name="username"
                                                type="text"
                                                class="h-10 min-w-0 flex-1 rounded-xl border-0 bg-transparent px-2 text-sm font-semibold text-gray-900 shadow-none focus:border-0 focus:bg-gray-50 focus:ring-0 disabled:cursor-default disabled:text-gray-700"
                                                value="{{ $initialUsername }}"
                                                pattern="[a-z0-9](?:[a-z0-9-]*[a-z0-9])?"
                                                x-ref="usernameInput"
                                                :readonly="!usernameEditing"
                                                @if(!$canEditUsername) disabled @endif
                                            />
                                            @if($canEditUsername)
                                                <button
                                                    type="button"
                                                    class="inline-flex h-10 items-center gap-2 rounded-xl px-4 text-sm font-bold transition focus:outline-none focus:ring-2 focus:ring-black"
                                                    :class="usernameEditing ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                                    :title="usernameEditing ? 'Lock username field' : 'Edit username'"
                                                    @click="usernameEditing = !usernameEditing; if (usernameEditing) { $nextTick(() => $refs.usernameInput.focus()) }"
                                                >
                                                    <i :class="usernameEditing ? 'fas fa-check text-xs' : 'fas fa-pencil-alt text-xs'"></i>
                                                    <span x-text="usernameEditing ? 'Done' : 'Edit'"></span>
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    class="inline-flex h-10 items-center gap-2 rounded-xl bg-gray-100 px-4 text-sm font-bold text-gray-600 transition hover:bg-gray-200 hover:text-black focus:outline-none focus:ring-2 focus:ring-black"
                                                    @click="usernameLockInfo = !usernameLockInfo"
                                                >
                                                    <i class="fas fa-lock text-xs"></i>
                                                    Verify Your Profile
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        {{ $canEditUsername ? 'Click the pencil to change your public handle. Use lowercase letters, numbers, and hyphens.' : 'Your username is generated automatically. Custom usernames are available after verification.' }}
                                    </p>
                                    @unless($canEditUsername)
                                        <div x-show="usernameLockInfo" x-cloak x-transition class="mt-3 max-w-xl rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-900">
                                            <div class="flex items-start gap-3">
                                                <i class="fas fa-shield-halved mt-0.5 text-yellow-700"></i>
                                                <div>
                                                    <p class="font-bold">Verification unlocks custom usernames.</p>
                                                    <p class="mt-1 text-yellow-800">It helps members trust your profile and gives you access to profile display controls such as custom handles and verified-only name options.</p>
                                                    <a href="{{ route('verification.create') }}" class="mt-3 inline-flex rounded-full bg-black px-4 py-2 text-xs font-bold text-white">Start Verification</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endunless
                                    @if($canEditUsername && $hasChangedUsernameBefore)
                                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                            <i class="fas fa-triangle-exclamation mr-2"></i>
                                            Changing your username again will stop your current custom username from working. Update any external links where you have shared it.
                                        </div>
                                    @endif
                                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                                </div>

                                <div class="mt-6">
                                    <x-input-label for="professional_name" value="Company / Professional Name" />
                                    <x-text-input id="professional_name" name="professional_name" type="text" class="mt-1 block w-full border-gray-300" value="{{ $companyName }}" placeholder="ALR Photography" />
                                    <p class="mt-2 text-xs text-gray-500">Shown beneath your name when enabled, or as the main display name when that option is unlocked.</p>
                                </div>

                                <div class="mt-6 rounded-2xl border border-gray-200 p-5 {{ !$isVerifiedProfile ? 'bg-gray-50 opacity-75' : 'bg-white' }}">
                                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                                                @if($profile->logo_path)
                                                    <img src="{{ asset($profile->logo_path) }}" alt="Current company logo" class="h-full w-full object-contain p-2">
                                                @else
                                                    <i class="fas fa-building text-2xl text-gray-400"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900">Company Logo</h4>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    {{ $isVerifiedProfile ? 'Upload a logo for your studio or company branding.' : 'Logo uploads are available to verified photographer profiles.' }}
                                                </p>
                                                @if(!$isVerifiedProfile)
                                                    <a href="{{ route('verification.create') }}" class="mt-2 inline-flex items-center text-sm font-medium text-yellow-700 hover:text-yellow-900">
                                                        <i class="fas fa-lock mr-2 text-xs"></i>
                                                        Verification required
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="sm:w-72">
                                            <label for="logo" class="sr-only">Company Logo</label>
                                            <input
                                                id="logo"
                                                name="logo"
                                                type="file"
                                                accept="image/jpeg,image/png"
                                                @disabled(!$isVerifiedProfile)
                                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-black file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-800 disabled:cursor-not-allowed disabled:bg-gray-100"
                                            >
                                            <p class="mt-2 text-xs text-gray-500">JPG or PNG. Max 2MB.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label value="Display Name As" />
                                        <div class="relative mt-1" @click.outside="displayNameDropdownOpen = false">
                                            <input type="hidden" name="display_name_format" x-model="displayNameFormat">
                                            <button type="button" @click="displayNameDropdownOpen = !displayNameDropdownOpen" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                <span x-text="displayNameFormatLabel()" class="text-gray-900"></span>
                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                            </button>
                                            <div x-show="displayNameDropdownOpen" x-cloak x-transition x-init="$watch('displayNameDropdownOpen', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                <template x-for="option in displayNameFormatOptions" :key="option.value">
                                                    <button type="button" @click="selectDisplayNameFormat(option.value)" class="flex w-full items-start justify-between gap-3 px-3 py-3 text-left text-sm transition hover:bg-gray-50" :class="displayNameFormat === option.value ? 'bg-gray-100 text-black' : option.locked ? 'bg-gray-50 text-gray-400' : 'text-gray-700'">
                                                        <span>
                                                            <span class="block font-medium" x-text="option.label"></span>
                                                            <span class="mt-0.5 block text-xs text-gray-500" x-text="option.description"></span>
                                                        </span>
                                                        <i x-show="option.locked" class="fas fa-lock mt-1 text-xs text-gray-400"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">Locked options are visible for clarity but require verification.</p>
                                    </div>

                                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-4 py-4 {{ !$isVerifiedProfile ? 'opacity-60' : '' }}">
                                        <input type="checkbox" name="show_company_on_profile" value="1" @checked(old('show_company_on_profile', $profile->show_company_on_profile)) @disabled(!$isVerifiedProfile) class="mt-1 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                        <span>
                                            <span class="block text-sm font-medium text-gray-900">Show company name on profile</span>
                                            <span class="block text-sm text-gray-500">{{ $isVerifiedProfile ? 'Display your company name directly beneath your profile name.' : 'Company display is available to verified profiles.' }}</span>
                                        </span>
                                    </label>
                                </div>

                                <div class="mt-6">
                                    <x-input-label for="bio" value="Bio" />
                                    <textarea id="bio" name="bio" rows="6" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black" placeholder="Tell models and collaborators about your style, experience, and approach.">{{ old('bio', $profile->bio) }}</textarea>
                                    <p class="mt-2 text-xs text-gray-500">Plain text only. Line breaks are preserved; HTML and embeds are stripped.</p>
                                </div>

                                <div class="mt-6 grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label value="Country" />
                                        <input type="hidden" name="location_country_code" x-model="selectedCountry">
                                        <input type="hidden" name="location_country" x-model="selectedCountryName">
                                        <div
                                            class="relative mt-1"
                                            x-data="searchableCountryDropdown({ countries: countries, selectedCode: selectedCountry, onSelect: (country) => selectCountry(country) })"
                                            x-init="init()"
                                            @click.outside="showDropdown = false"
                                        >
                                            <input
                                                type="text"
                                                x-model="searchInput"
                                                @focus="showDropdown = true; filterCountries()"
                                                @input="showDropdown = true; filterCountries()"
                                                @keydown.arrow-down.prevent="highlightNext()"
                                                @keydown.arrow-up.prevent="highlightPrevious()"
                                                @keydown.enter.prevent="selectHighlighted()"
                                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-black focus:ring-black"
                                                placeholder="Type to search country..."
                                            >
                                            <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                <template x-for="(country, index) in filteredCountries" :key="country.code">
                                                    <button type="button" @click="selectCountry(country)" class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-gray-50" :class="index === highlightedIndex ? 'bg-gray-100' : ''">
                                                        <span x-text="country.name"></span>
                                                        <span class="text-xs text-gray-400" x-text="country.code"></span>
                                                    </button>
                                                </template>
                                                <p x-show="filteredCountries.length === 0" class="px-3 py-2 text-sm text-gray-500">No countries found.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <x-input-label for="location_city" value="City" />
                                        <input type="hidden" name="location_geoname_id" x-model="selectedGeonameId">
                                        <x-text-input id="location_city" name="location_city" type="text" class="mt-1 block w-full border-gray-300" x-model="cityInput" @input="searchCities()" @focus="searchCities()" placeholder="Start typing your city..." />
                                        <div x-show="showSuggestions" x-cloak class="absolute z-40 mt-1 max-h-60 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                            <template x-for="suggestion in suggestions" :key="suggestion.id">
                                                <button type="button" @click="selectCity(suggestion)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50">
                                                    <span class="font-medium" x-text="suggestion.city"></span>
                                                    <span class="text-gray-500" x-text="suggestion.admin_name ? ', ' + suggestion.admin_name : ''"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label for="date_of_birth" value="Date of Birth" />
                                        <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full border-gray-300" value="{{ old('date_of_birth', optional($profile->date_of_birth)->format('Y-m-d')) }}" required />
                                    </div>

                                    <div>
                                        <x-input-label value="Gender" />
                                        <div class="relative mt-1" x-data="customSelect({ options: genderOptions, selectedValue: @js(old('gender', $profile->gender ?? '')) })" @click.outside="showDropdown = false">
                                            <input type="hidden" name="gender" x-model="selectedValue">
                                            <button type="button" @click="showDropdown = !showDropdown" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                <span x-text="selectedLabel || 'Select gender...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                            </button>
                                            <div x-show="showDropdown" x-cloak x-transition class="absolute z-50 mt-1 w-full overflow-hidden rounded-md border border-gray-300 bg-white shadow-xl">
                                                <template x-for="option in options" :key="option.value">
                                                    <button type="button" @click="selectOption(option.value)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50" x-text="option.label"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="section-professional">
                            <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                                <div class="mb-6">
                                    <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                        <i class="fas fa-briefcase text-gray-400"></i>
                                        <span>Professional Details</span>
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Help members quickly understand your experience, location, and the types of shoots you offer.</p>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label value="Experience Level" />
                                        <div class="relative mt-1" x-data="customSelect({ options: experienceOptions, selectedValue: @js(old('experience_level', $profile->experience_level ?? '')) })" @click.outside="showDropdown = false">
                                            <input type="hidden" name="experience_level" x-model="selectedValue">
                                            <button type="button" @click="showDropdown = !showDropdown" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                <span x-text="selectedLabel || 'Select experience...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                            </button>
                                            <div x-show="showDropdown" x-cloak x-transition class="absolute z-50 mt-1 w-full overflow-hidden rounded-md border border-gray-300 bg-white shadow-xl">
                                                <template x-for="option in options" :key="option.value">
                                                    <button type="button" @click="selectOption(option.value)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50" x-text="option.label"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label for="experience_start_year" value="Started Photography In" />
                                        <x-text-input id="experience_start_year" name="experience_start_year" type="number" min="1900" max="{{ date('Y') }}" class="mt-1 block w-full border-gray-300" value="{{ old('experience_start_year', $profile->experience_start_year) }}" placeholder="{{ date('Y') - 5 }}" />
                                    </div>

                                    <div>
                                        <x-input-label for="studio_location" value="Studio / Base Location" />
                                        <x-text-input id="studio_location" name="studio_location" type="text" class="mt-1 block w-full border-gray-300" value="{{ old('studio_location', $profile->studio_location) }}" placeholder="Cluj-Napoca, Romania" />
                                    </div>

                                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-4 py-4">
                                        <input type="checkbox" name="available_for_travel" value="1" @checked(old('available_for_travel', $profile->available_for_travel)) class="mt-1 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                        <span>
                                            <span class="block text-sm font-medium text-gray-900">Available for travel</span>
                                            <span class="block text-sm text-gray-500">Let members know you can work outside your base location.</span>
                                        </span>
                                    </label>
                                </div>

                                <div class="mt-8 grid gap-8 lg:grid-cols-2">
                                    <div>
                                        <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Specialties</h4>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            @foreach($specialtiesOptions as $key => $label)
                                                <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm">
                                                    <input type="checkbox" name="specialties[]" value="{{ $key }}" @checked(in_array($key, $selectedSpecialties, true)) class="mt-0.5 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Services</h4>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            @foreach($servicesOptions as $key => $label)
                                                <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-3 py-3 text-sm">
                                                    <input type="checkbox" name="services_offered[]" value="{{ $key }}" @checked(in_array($key, $selectedServices, true)) class="mt-0.5 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="section-equipment">
                            <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                                <div class="mb-6">
                                    <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                        <i class="fas fa-camera text-gray-400"></i>
                                        <span>Equipment</span>
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Keep this practical. Add one item per line so it stays easy to scan on your profile.</p>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="equipment_cameras" value="Cameras" />
                                        <textarea id="equipment_cameras" name="equipment_cameras" rows="5" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black" placeholder="Canon R5&#10;Sony A7 IV">{{ old('equipment_cameras', $equipmentText('cameras')) }}</textarea>
                                    </div>
                                    <div>
                                        <x-input-label for="equipment_lenses" value="Lenses" />
                                        <textarea id="equipment_lenses" name="equipment_lenses" rows="5" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black" placeholder="50mm f/1.2&#10;24-70mm f/2.8">{{ old('equipment_lenses', $equipmentText('lenses')) }}</textarea>
                                    </div>
                                    <div>
                                        <x-input-label for="equipment_lighting" value="Lighting" />
                                        <textarea id="equipment_lighting" name="equipment_lighting" rows="5" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black" placeholder="Profoto B10&#10;Softbox kit">{{ old('equipment_lighting', $equipmentText('lighting')) }}</textarea>
                                    </div>
                                    <div>
                                        <x-input-label for="equipment_other" value="Other Kit" />
                                        <textarea id="equipment_other" name="equipment_other" rows="5" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black" placeholder="Backdrop system&#10;Tethering station">{{ old('equipment_other', $equipmentText('other')) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="section-contact">
                            <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                                <div class="mb-6">
                                    <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                        <span>Contact & Links</span>
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Requests are handled through the platform; this email is used for notifications rather than being exposed publicly.</p>
                                </div>

                                <div class="space-y-6">
                                    <div class="grid gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="public_email" value="Professional Email" />
                                        <x-text-input id="public_email" name="public_email" type="email" class="mt-1 block w-full border-gray-300" value="{{ old('public_email', $profile->public_email ?: $user->email) }}" />
                                    </div>
                                    <div>
                                        <x-input-label for="phone" value="Phone (optional)" />
                                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full border-gray-300" value="{{ old('phone', $profile->phone) }}" />
                                    </div>
                                    </div>

                                    <div>
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <x-input-label value="Social Links" />
                                                <p class="mt-2 text-sm text-gray-500">Add whichever public platforms you actually want shown on your profile.</p>
                                            </div>
                                            <button type="button" @click="addSocialLink()" class="inline-flex items-center justify-center rounded-md border border-black px-3 py-2 text-sm font-medium text-black transition hover:bg-gray-100">
                                                <i class="fas fa-plus mr-2 text-xs"></i>
                                                Add Link
                                            </button>
                                        </div>

                                        <div class="mt-4 space-y-3" x-show="socialLinks.length > 0">
                                            <template x-for="(link, index) in socialLinks" :key="link.uid">
                                                <div class="grid gap-3 rounded-xl border border-gray-200 p-4 xl:grid-cols-[220px_minmax(0,1fr)_auto]">
                                                    <div>
                                                        <label class="mb-1 block text-sm font-medium text-gray-700">Platform</label>
                                                        <div class="relative" x-data="customSelect({
                                                            options: @js($socialPlatformOptions),
                                                            selectedValue: link.platform || '',
                                                            onSelect: (value) => { link.platform = value; }
                                                        })" x-init="init()" x-effect="syncFromExternal(link.platform || '')">
                                                            <input type="hidden" :name="`social_links[${index}][platform]`" x-model="selectedValue">
                                                            <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                                <span x-text="selectedLabel || 'Select platform...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                                            </button>
                                                            <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                                <template x-for="(option, optionIndex) in options" :key="`social-platform-${option.value || optionIndex}`">
                                                                    <button type="button" @click="selectOption(option.value)" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                                        <span x-text="option.label"></span>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label class="mb-1 block text-sm font-medium text-gray-700">URL</label>
                                                        <input type="url" x-model="link.url" class="block w-full rounded-md border border-gray-300 shadow-sm focus:border-black focus:ring-black" :placeholder="socialPlaceholder(link.platform)">
                                                    </div>

                                                    <div class="flex items-end">
                                                        <button type="button" @click="removeSocialLink(index)" class="inline-flex items-center rounded-md border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div x-show="socialLinks.length === 0" class="mt-4 rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">
                                            No social links added yet.
                                        </div>

                                        <template x-for="(link, index) in socialLinks" :key="`hidden-social-${link.uid}`">
                                            <div>
                                                <input type="hidden" :name="`social_links[${index}][platform]`" :value="link.platform">
                                                <input type="hidden" :name="`social_links[${index}][url]`" :value="link.url">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="section-visibility">
                            <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                                <div class="mb-6">
                                    <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                        <i class="fas fa-eye text-gray-400"></i>
                                        <span>Visibility</span>
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Control whether the profile is visible and whether portfolio content needs NSFW handling.</p>
                                </div>

                                <div class="grid gap-4 xl:grid-cols-2">
                                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-4 py-4">
                                        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $profile->is_public ?? true)) class="mt-1 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                        <span>
                                            <span class="block text-sm font-medium text-gray-900">Public profile</span>
                                            <span class="block text-sm text-gray-500">Allow visitors and members to view your photographer profile.</span>
                                        </span>
                                    </label>

                                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-4 py-4">
                                        <input type="checkbox" name="contains_nudity" value="1" @checked(old('contains_nudity', $profile->contains_nudity)) class="mt-1 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                        <span>
                                            <span class="block text-sm font-medium text-gray-900">Portfolio contains NSFW content</span>
                                            <span class="block text-sm text-gray-500">Used for visibility warnings and age-gated presentation where needed.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route('photographers.show', $user->profileRouteIdentifier()) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" :disabled="saving" class="inline-flex items-center justify-center rounded-md bg-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-60">
                            <i x-show="saving" class="fas fa-spinner fa-spin mr-2"></i>
                            <span x-text="saving ? 'Saving...' : 'Save Profile'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function customSelect(config) {
        return {
            options: config.options || [],
            selectedValue: config.selectedValue || '',
            selectedLabel: '',
            showDropdown: false,

            init() {
                this.syncSelectedLabel();
            },

            syncSelectedLabel() {
                const selected = this.options.find((option) => option.value === this.selectedValue);
                this.selectedLabel = selected ? selected.label : '';
            },

            syncFromExternal(value) {
                if (this.selectedValue !== value) {
                    this.selectedValue = value;
                }
                this.syncSelectedLabel();
            },

            selectOption(value) {
                const selected = this.options.find((option) => option.value === value);
                if (selected && selected.locked) {
                    return;
                }

                this.selectedValue = value;
                this.syncSelectedLabel();
                this.showDropdown = false;

                if (typeof config.onSelect === 'function') {
                    config.onSelect(value);
                }
            },
        };
    }

    function searchableCountryDropdown(config = {}) {
        return {
            countries: [],
            filteredCountries: [],
            searchInput: '',
            showDropdown: false,
            highlightedIndex: -1,

            init() {
                this.countries = Object.keys(config.countries || {}).map((code) => ({
                    code,
                    name: config.countries[code],
                })).sort((a, b) => a.name.localeCompare(b.name));

                const selected = this.countries.find((country) => country.code === config.selectedCode);
                if (selected) {
                    this.searchInput = selected.name;
                }

                this.filteredCountries = this.countries.slice(0, 50);
            },

            filterCountries() {
                const query = this.searchInput.toLowerCase().trim();
                this.filteredCountries = query
                    ? this.countries.filter((country) => country.name.toLowerCase().includes(query) || country.code.toLowerCase().includes(query))
                    : this.countries.slice(0, 50);
                this.highlightedIndex = -1;
            },

            selectCountry(country) {
                this.searchInput = country.name;
                this.showDropdown = false;
                if (typeof config.onSelect === 'function') {
                    config.onSelect(country);
                }
            },

            highlightNext() {
                if (this.highlightedIndex < this.filteredCountries.length - 1) {
                    this.highlightedIndex++;
                }
            },

            highlightPrevious() {
                if (this.highlightedIndex > 0) {
                    this.highlightedIndex--;
                }
            },

            selectHighlighted() {
                if (this.highlightedIndex >= 0 && this.filteredCountries[this.highlightedIndex]) {
                    this.selectCountry(this.filteredCountries[this.highlightedIndex]);
                }
            },
        };
    }

    window.positionFloatingDropdown = function positionFloatingDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        const container = dropdown.parentElement;
        const trigger = container ? container.querySelector('input[type="text"], button') : null;

        if (!trigger || typeof trigger.getBoundingClientRect !== 'function') {
            dropdown.classList.remove('bottom-full', 'mb-1');
            dropdown.classList.add('mt-1');
            dropdown.style.maxHeight = '240px';
            return;
        }

        const rect = trigger.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;

        if (spaceBelow < 200 && spaceAbove > spaceBelow) {
            dropdown.classList.add('bottom-full', 'mb-1');
            dropdown.classList.remove('mt-1');
            dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
            return;
        }

        dropdown.classList.remove('bottom-full', 'mb-1');
        dropdown.classList.add('mt-1');
        dropdown.style.maxHeight = Math.min(Math.max(spaceBelow - 20, 120), 240) + 'px';
    };

    function photographerProfileEditor() {
        return {
            countries: @json($countries),
            selectedCountry: @json($locationCountryCode),
            selectedCountryName: @json($locationCountryCode ? ($countries[$locationCountryCode] ?? old('location_country', $profile->location_country ?? '')) : old('location_country', $profile->location_country ?? '')),
            cityInput: @json(old('location_city', $profile->location_city ?? '')),
            selectedGeonameId: @json(old('location_geoname_id', $profile->location_geoname_id ?? null)),
            usernameEditing: @json($errors->has('username')),
            usernameLockInfo: false,
            displayNameFormat: @json($initialDisplayNameFormat),
            displayNameFormatOptions: @json($displayNameFormatOptions),
            displayNameDropdownOpen: false,
            socialLinks: @json($initialSocialLinks),
            genderOptions: [
                { value: 'male', label: 'Male' },
                { value: 'female', label: 'Female' },
                { value: 'other', label: 'Other' },
            ],
            experienceOptions: [
                { value: 'beginner', label: 'Beginner' },
                { value: 'intermediate', label: 'Intermediate' },
                { value: 'advanced', label: 'Advanced' },
                { value: 'professional', label: 'Professional' },
            ],
            suggestions: [],
            showSuggestions: false,
            searchTimeout: null,
            saving: false,
            toast: {
                show: false,
                type: 'success',
                message: @json(session('status')),
            },

            init() {
                if (this.toast.message) {
                    this.showToast(this.toast.message);
                }
            },

            displayNameFormatLabel() {
                const selected = this.displayNameFormatOptions.find((option) => option.value === this.displayNameFormat);
                return selected ? selected.label : 'Choose display format...';
            },

            selectDisplayNameFormat(value) {
                const selected = this.displayNameFormatOptions.find((option) => option.value === value);
                if (selected && selected.locked) {
                    return;
                }

                this.displayNameFormat = value;
                this.displayNameDropdownOpen = false;
            },

            selectCountry(country) {
                this.selectedCountry = country.code;
                this.selectedCountryName = country.name;
                this.selectedGeonameId = null;
                this.cityInput = '';
                this.suggestions = [];
                this.showSuggestions = false;
            },

            searchCities() {
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }

                if (!this.selectedCountry || this.cityInput.length < 2) {
                    this.suggestions = [];
                    this.showSuggestions = false;
                    return;
                }

                this.searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`/api/locations?q=${encodeURIComponent(this.cityInput)}&country=${encodeURIComponent(this.selectedCountry)}`);
                        const data = await response.json();
                        this.suggestions = data.data || [];
                        this.showSuggestions = this.suggestions.length > 0;
                    } catch (error) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                    }
                }, 250);
            },

            selectCity(suggestion) {
                this.cityInput = suggestion.city;
                this.selectedGeonameId = suggestion.id;
                this.selectedCountryName = suggestion.country_name;
                this.showSuggestions = false;
            },

            socialPlaceholder(platform) {
                const placeholders = {
                    instagram: 'https://instagram.com/yourname',
                    facebook: 'https://facebook.com/yourname',
                    x: 'https://x.com/yourname',
                    tiktok: 'https://tiktok.com/@yourname',
                    youtube: 'https://youtube.com/@yourname',
                    behance: 'https://behance.net/yourname',
                    linkedin: 'https://linkedin.com/in/yourname',
                    website: 'https://yourportfolio.com',
                };

                return placeholders[platform] || 'https://example.com';
            },

            addSocialLink() {
                this.socialLinks.push({
                    platform: '',
                    url: '',
                    uid: `social_${Date.now()}_${Math.random().toString(36).slice(2)}`,
                });
            },

            removeSocialLink(index) {
                this.socialLinks.splice(index, 1);
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => {
                    this.toast.show = false;
                }, 3200);
            },

            async submitProfile(event) {
                if (this.saving) {
                    return;
                }

                this.saving = true;
                const form = event.target;
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const firstError = payload.errors
                            ? Object.values(payload.errors).flat()[0]
                            : payload.message;
                        this.showToast(firstError || 'Unable to save profile.', 'error');
                        return;
                    }

                    this.showToast(payload.message || 'Profile updated successfully.');
                } catch (error) {
                    this.showToast('Unable to save profile. Please try again.', 'error');
                } finally {
                    this.saving = false;
                }
            },
        };
    }
</script>
