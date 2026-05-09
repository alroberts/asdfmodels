@php
    $extractCm = function ($value) {
        if (preg_match('/([0-9]+(?:\.[0-9]+)?)\s*cm/i', (string) $value, $matches)) {
            return $matches[1];
        }

        return null;
    };

    $extractMappedSize = function ($value, array $labels) {
        $value = trim((string) $value);

        foreach ($labels as $key => $label) {
            $prefix = $label . ' ';
            if (str_starts_with($value, $prefix)) {
                return [$key, trim(substr($value, strlen($prefix)))];
            }
        }

        return [null, null];
    };

    [$legacyShoeRegion, $legacyShoeValue] = $extractMappedSize($profile->shoe_size, $shoeSizeRegions);
    [$legacyDressRegion, $legacyDressValue] = $extractMappedSize($profile->dress_size, $dressSizeRegions);

    $initialHeightCm = old('height_cm', $profile->height_cm ?? $extractCm($profile->height));
    $initialChestCm = old('chest_cm', $profile->chest_cm ?? $extractCm($profile->chest));
    $initialWaistCm = old('waist_cm', $profile->waist_cm ?? $extractCm($profile->waist));
    $initialInseamCm = old('inseam_cm', $profile->inseam_cm ?? $extractCm($profile->inseam));
    $initialBustCm = old('bust_cm', $profile->bust_cm ?? $extractCm($profile->bust));
    $initialHipsCm = old('hips_cm', $profile->hips_cm ?? $extractCm($profile->hips));
    $initialShoeRegion = old('shoe_size_region', $profile->shoe_size_region ?? $legacyShoeRegion);
    $initialShoeValue = old('shoe_size_value', $profile->shoe_size_value ?? $legacyShoeValue);
    $initialDressRegion = old('dress_size_region', $profile->dress_size_region ?? $legacyDressRegion);
    $initialDressValue = old('dress_size_value', $profile->dress_size_value ?? $legacyDressValue);
    $initialMeasurementSystem = old(
        'measurement_system',
        ($profile->measurement_system === 'imperial' ? 'us_customary' : $profile->measurement_system)
            ?: \App\Helpers\ModelProfileOptions::defaultMeasurementSystemForCountry($profile->location_country_code)
    );
    $initialFirstName = old('first_name', $user->first_name ?? '');
    $initialLastName = old('last_name', $user->last_name ?? '');
    $defaultDisplayNameFormat = $profile->isVerified() ? 'full_name' : 'first_name_last_initial';
    $initialDisplayNameFormat = old(
        'display_name_format',
        ($profile->display_name_format && !($profile->display_name_format === 'full_name' && !$profile->isVerified()))
            ? $profile->display_name_format
            : $defaultDisplayNameFormat
    );

    $displayNameFormatOptions = \App\Helpers\ModelProfileOptions::displayNameFormatOptionsForNames(
        $initialFirstName,
        $initialLastName,
        $profile->isVerified()
    );

    $measurementIcons = [
        'height' => asset('assets/graphics/model-icons/height-clean.png'),
        'male_chest' => asset('assets/graphics/model-icons/m-chest-clean.png'),
        'female_bust' => asset('assets/graphics/model-icons/f-bust-clean.png'),
        'male_waist' => asset('assets/graphics/model-icons/m-waist-clean.png'),
        'female_waist' => asset('assets/graphics/model-icons/f-waist-clean.png'),
        'male_inseam' => asset('assets/graphics/model-icons/m-inseam-clean.png'),
        'female_hips' => asset('assets/graphics/model-icons/f-hips-clean.png'),
        'male_suit' => asset('assets/graphics/model-icons/m-suit-clean.png'),
        'male_shoe' => asset('assets/graphics/model-icons/m-shoe-size-clean.png'),
        'female_shoe' => asset('assets/graphics/model-icons/f-shoe-size-clean.png'),
        'female_dress' => asset('assets/graphics/model-icons/f-dress-size-clean.png'),
        'male_hair' => asset('assets/graphics/model-icons/m-hair-clean.png'),
        'female_hair' => asset('assets/graphics/model-icons/f-hair-clean.png'),
        'eyes' => asset('assets/graphics/model-icons/eye-colour-clean.png'),
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
        if (filled(old('instagram', $profile->instagram))) {
            $initialSocialLinks[] = [
                'platform' => 'instagram',
                'url' => old('instagram', $profile->instagram),
                'uid' => uniqid('social_', true),
            ];
        }

        if (filled(old('portfolio_website', $profile->portfolio_website))) {
            $initialSocialLinks[] = [
                'platform' => 'website',
                'url' => old('portfolio_website', $profile->portfolio_website),
                'uid' => uniqid('social_', true),
            ];
        }
    }

    $socialPlatformOptions = [
        ['value' => '', 'label' => 'Select platform...'],
        ['value' => 'instagram', 'label' => 'Instagram'],
        ['value' => 'facebook', 'label' => 'Facebook'],
        ['value' => 'x', 'label' => 'X'],
        ['value' => 'tiktok', 'label' => 'TikTok'],
        ['value' => 'youtube', 'label' => 'YouTube'],
        ['value' => 'behance', 'label' => 'Behance'],
        ['value' => 'linkedin', 'label' => 'LinkedIn'],
        ['value' => 'website', 'label' => 'Website / Portfolio'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-500">Model Profile</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-gray-900">Edit Your Profile</h2>
                <p class="mt-2 max-w-2xl text-sm text-gray-600">
                    This is your main profile editor. Update your public details, measurements, specialties, and contact preferences without stepping through onboarding again.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('models.show', $user->id) }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    View Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="modelProfileEditor()" x-init="init()">
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
                class="fixed right-6 top-24 z-[70] max-w-sm rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg"
            >
                <div class="flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 text-green-600"></i>
                    <div class="flex-1">
                        <p class="font-medium text-green-900" x-text="toast.message"></p>
                    </div>
                    <button type="button" @click="toast.show = false" class="text-green-700 transition hover:text-green-900">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6 md:hidden">
                <div class="overflow-x-auto">
                    <nav class="flex min-w-max gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm">
                        <a href="#section-basics" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Basics</a>
                        <a href="#section-measurements" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Measurements</a>
                        <a href="#section-professional" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-black">Professional</a>
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
                            <a href="#section-measurements" class="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 hover:text-black">
                                <i class="fas fa-ruler-combined w-4 text-center text-gray-400"></i>
                                <span>Measurements & Appearance</span>
                            </a>
                            <a href="#section-professional" class="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 hover:text-black">
                                <i class="fas fa-briefcase w-4 text-center text-gray-400"></i>
                                <span>Professional Details</span>
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
                                        Verified profiles feel more trustworthy to photographers.
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

                <form method="POST" action="{{ route('profile.model.update') }}" class="min-w-0 md:w-3/4 md:flex-1">
                    @csrf
                    @method('patch')

                    <div class="space-y-0">
                        <section id="section-basics">
                            <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                            <div class="mb-6">
                                <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                    <span>Basic Information</span>
                                </h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">The main details that set the tone for your profile.</p>
                            </div>

                            <div class="space-y-6">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <x-input-label for="first_name" :value="__('First Name')" />
                                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="$initialFirstName" required autocomplete="given-name" />
                                    <p class="mt-2 text-xs text-gray-500">This is the public-facing first name used throughout the platform.</p>
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="last_name" :value="__('Last Name')" />
                                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="$initialLastName" required autocomplete="family-name" />
                                    <p class="mt-2 text-xs text-gray-500">The last name is stored separately so it can be hidden from public display.</p>
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="display_name_format" :value="__('Display Name As')" />
                                <div class="relative mt-1" @click.outside="displayNameDropdownOpen = false">
                                    <input type="hidden" id="display_name_format" name="display_name_format" x-model="displayNameFormat" />
                                    <button type="button" @click="displayNameDropdownOpen = !displayNameDropdownOpen" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                        <span x-text="displayNameFormatLabel()" :class="displayNameFormat ? 'text-gray-900' : 'text-gray-400'"></span>
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </button>
                                    <div x-show="displayNameDropdownOpen" x-cloak x-transition x-init="$watch('displayNameDropdownOpen', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                        <template x-for="(option, index) in displayNameFormatOptions" :key="index">
                                            <button
                                                type="button"
                                                @mousedown.prevent="selectDisplayNameFormat(option.value)"
                                                @click.prevent
                                                class="flex w-full cursor-pointer items-start gap-3 border-b border-gray-100 px-4 py-2.5 text-left last:border-b-0 hover:bg-gray-50"
                                                :class="displayNameFormat === option.value ? 'bg-gray-100 text-black' : option.locked ? 'bg-gray-50 text-gray-400' : 'text-gray-700'"
                                            >
                                                <span class="mt-0.5 shrink-0">
                                                    <i x-show="option.locked" class="fas fa-lock text-xs text-gray-400"></i>
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block font-medium" x-text="option.label"></span>
                                                    <span x-show="option.description" class="block text-xs text-gray-400" x-text="option.description"></span>
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    The full-name option stays visible for clarity, but only verified members can actually select it.
                                </p>
                                <x-input-error :messages="$errors->get('display_name_format')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="bio" :value="__('Bio')" />
                                <x-textarea id="bio" name="bio" rows="5" class="mt-1 block w-full">{{ old('bio', $profile->bio) }}</x-textarea>
                                <p class="mt-2 text-xs text-gray-500">Write a concise introduction that helps photographers understand your style and experience.</p>
                                <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                            </div>

                            <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-4">
                                <div>
                                    <x-input-label for="location_country_code" :value="__('Country')" />
                                    <div class="relative mt-1" x-data="searchableDropdown({
                                        onSelect: (country) => { selectedCountry = country.code; selectedCountryName = country.name; handleCountryChange(); }
                                    })" x-init="initCountries(@js($countries), selectedCountry)">
                                        <div class="relative">
                                            <x-text-input
                                                id="location_country_search"
                                                type="text"
                                                x-model="searchInput"
                                                @input="filterCountries()"
                                                @focus="showDropdown = true; if (filteredCountries.length === 0 && countries.length > 0) { filteredCountries = countries.slice(0, 50); }"
                                                @blur="setTimeout(() => showDropdown = false, 200)"
                                                @keydown.arrow-down.prevent="highlightNext()"
                                                @keydown.arrow-up.prevent="highlightPrevious()"
                                                @keydown.enter.prevent="selectHighlighted()"
                                                @keydown.escape="showDropdown = false"
                                                class="block w-full pr-10"
                                                placeholder="Type to search countries..."
                                                autocomplete="off"
                                            />
                                            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                            </div>
                                        </div>
                                        <input type="hidden" id="location_country_code" name="location_country_code" x-model="selectedValue" />
                                        <div
                                            x-show="showDropdown && filteredCountries.length > 0"
                                            x-cloak
                                            x-transition
                                            x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                            class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl"
                                            style="max-height: 240px;"
                                        >
                                            <template x-for="(country, index) in filteredCountries" :key="country.code">
                                                <div
                                                    @click="selectCountry(country)"
                                                    @mouseenter="highlightedIndex = index"
                                                    :class="{ 'bg-gray-100 text-black': index === highlightedIndex || selectedValue === country.code, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== country.code }"
                                                    class="cursor-pointer border-b border-gray-100 px-4 py-2.5 last:border-b-0"
                                                >
                                                    <div class="font-medium" x-text="country.name"></div>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="showDropdown && filteredCountries.length === 0" x-cloak class="absolute z-50 w-full rounded-md border border-gray-300 bg-white p-4 text-center text-gray-500 shadow-xl">
                                            No countries found
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('location_country_code')" class="mt-2" />
                                </div>

                                <div class="xl:col-span-2">
                                    <x-input-label for="location_city" :value="__('City')" />
                                    <div class="relative">
                                        <x-text-input
                                            id="location_city"
                                            name="location_city"
                                            type="text"
                                            class="mt-1 block w-full"
                                            x-model="cityInput"
                                            @input="searchCities()"
                                            @focus="showSuggestions = true"
                                            @blur="setTimeout(() => showSuggestions = false, 200)"
                                            placeholder="Start typing city name..."
                                            autocomplete="off"
                                        />
                                        <input type="hidden" name="location_geoname_id" x-model="selectedGeonameId">
                                        <input type="hidden" name="location_country" x-model="selectedCountryName">

                                        <div x-show="showSuggestions && suggestions.length > 0" x-cloak class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg">
                                            <template x-for="suggestion in suggestions" :key="suggestion.id">
                                                <button type="button" @click="selectCity(suggestion)" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50">
                                                    <div class="font-medium text-gray-900" x-text="suggestion.city"></div>
                                                    <div class="text-xs text-gray-500" x-text="suggestion.label"></div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('location_city')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                                    <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($profile->date_of_birth)->format('Y-m-d'))" />
                                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                <div>
                                    <x-input-label for="gender" :value="__('Gender')" />
                                    <div class="relative mt-1" x-data="customSelect({
                                        options: [
                                            { value: '', label: 'Select gender...' },
                                            { value: 'male', label: 'Male' },
                                            { value: 'female', label: 'Female' },
                                            { value: 'other', label: 'Other' }
                                        ],
                                        selectedValue: gender || '',
                                        onSelect: (value) => { gender = value; applyDefaultSizeRegions(); }
                                    })" x-init="init()">
                                        <input type="hidden" id="gender" name="gender" x-model="selectedValue" />
                                        <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                            <span x-text="selectedLabel || 'Select gender...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                        </button>
                                        <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                            <template x-for="(option, index) in options" :key="index">
                                                <button type="button" @click="selectOption(option.value)" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                    <span x-text="option.label"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                                </div>
                            </div>
                            </div>
                        </section>

                        <section id="section-measurements">
                        <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                            <div class="mb-6">
                                <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                    <i class="fas fa-ruler-combined text-gray-400"></i>
                                    <span>Measurements & Appearance</span>
                                </h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Keep the profile structured and easy to compare across members.</p>
                            </div>

                            <div class="mb-8">
                                <x-input-label for="measurement_system" :value="__('Measurement System')" />
                                <div class="relative mt-1" x-data="customSelect({
                                    options: @js(collect($measurementSystems)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all()),
                                    selectedValue: measurementSystem || ''
                                })" x-init="init()" x-effect="syncFromExternal(measurementSystem)">
                                    <input type="hidden" id="measurement_system" name="measurement_system" x-model="selectedValue" />
                                    <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                        <span x-text="selectedLabel || 'Select system...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                    </button>
                                    <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                        <template x-for="(option, index) in options" :key="index">
                                            <button type="button" @click="selectOption(option.value); changeMeasurementSystem(option.value)" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                <span x-text="option.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Suggested automatically from your country, but always editable.</p>
                                <x-input-error :messages="$errors->get('measurement_system')" class="mt-2" />
                            </div>

                            <div class="space-y-8">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-500">Body Measurements</p>
                                <div class="mt-4 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                                    <div>
                                        <label for="height_cm" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50">
                                                <img src="{{ $measurementIcons['height'] }}" alt="" class="h-8 w-8 object-contain">
                                            </span>
                                            <span>Height (cm)</span>
                                        </label>
                                        <input type="hidden" id="height_cm" name="height_cm" x-model="heightCm">
                                        <template x-if="usesMetricHeight()">
                                            <x-text-input type="number" min="100" max="250" step="1" class="mt-1 block w-full" x-model="heightDisplay" @input="updateCanonicalMeasurements()" />
                                        </template>
                                        <template x-if="!usesMetricHeight()">
                                            <div class="mt-1 grid grid-cols-2 gap-3">
                                                <x-text-input type="number" min="3" max="8" step="1" x-model="heightFeet" @input="updateCanonicalMeasurements()" placeholder="ft" />
                                                <x-text-input type="number" min="0" max="11" step="1" x-model="heightInches" @input="updateCanonicalMeasurements()" placeholder="in" />
                                            </div>
                                        </template>
                                        <x-input-error :messages="$errors->get('height_cm')" class="mt-2" />
                                    </div>

                                    <div x-show="isMale()" x-cloak>
                                        <label for="chest_cm" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50">
                                                <img src="{{ $measurementIcons['male_chest'] }}" alt="" class="h-8 w-8 object-contain">
                                            </span>
                                            <span x-text="bodyMeasurementLabel('Chest')"></span>
                                        </label>
                                        <input type="hidden" id="chest_cm" name="chest_cm" x-model="chestCm">
                                        <x-text-input type="number" min="20" max="200" step="0.1" class="mt-1 block w-full" x-model="chestDisplay" @input="updateCanonicalMeasurements()" />
                                        <x-input-error :messages="$errors->get('chest_cm')" class="mt-2" />
                                    </div>

                                    <div x-show="isFemale()" x-cloak>
                                        <label for="bust_cm" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50">
                                                <img src="{{ $measurementIcons['female_bust'] }}" alt="" class="h-8 w-8 object-contain">
                                            </span>
                                            <span x-text="bodyMeasurementLabel('Bust')"></span>
                                        </label>
                                        <input type="hidden" id="bust_cm" name="bust_cm" x-model="bustCm">
                                        <x-text-input type="number" min="20" max="200" step="0.1" class="mt-1 block w-full" x-model="bustDisplay" @input="updateCanonicalMeasurements()" />
                                        <x-input-error :messages="$errors->get('bust_cm')" class="mt-2" />
                                    </div>

                                    <div>
                                        <label for="waist_cm" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50">
                                                <img x-show="isFemale()" x-cloak src="{{ $measurementIcons['female_waist'] }}" alt="" class="h-8 w-8 object-contain">
                                                <img x-show="!isFemale()" x-cloak src="{{ $measurementIcons['male_waist'] }}" alt="" class="h-8 w-8 object-contain">
                                            </span>
                                            <span x-text="bodyMeasurementLabel('Waist')"></span>
                                        </label>
                                        <input type="hidden" id="waist_cm" name="waist_cm" x-model="waistCm">
                                        <x-text-input type="number" min="20" max="200" step="0.1" class="mt-1 block w-full" x-model="waistDisplay" @input="updateCanonicalMeasurements()" />
                                        <x-input-error :messages="$errors->get('waist_cm')" class="mt-2" />
                                    </div>

                                    <div x-show="isMale()" x-cloak>
                                        <label for="inseam_cm" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50">
                                                <img src="{{ $measurementIcons['male_inseam'] }}" alt="" class="h-8 w-8 object-contain">
                                            </span>
                                            <span x-text="bodyMeasurementLabel('Inseam')"></span>
                                        </label>
                                        <input type="hidden" id="inseam_cm" name="inseam_cm" x-model="inseamCm">
                                        <x-text-input type="number" min="20" max="150" step="0.1" class="mt-1 block w-full" x-model="inseamDisplay" @input="updateCanonicalMeasurements()" />
                                        <x-input-error :messages="$errors->get('inseam_cm')" class="mt-2" />
                                    </div>

                                    <div x-show="isFemale()" x-cloak>
                                        <label for="hips_cm" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50">
                                                <img src="{{ $measurementIcons['female_hips'] }}" alt="" class="h-8 w-8 object-contain">
                                            </span>
                                            <span x-text="bodyMeasurementLabel('Hips')"></span>
                                        </label>
                                        <input type="hidden" id="hips_cm" name="hips_cm" x-model="hipsCm">
                                        <x-text-input type="number" min="20" max="200" step="0.1" class="mt-1 block w-full" x-model="hipsDisplay" @input="updateCanonicalMeasurements()" />
                                        <x-input-error :messages="$errors->get('hips_cm')" class="mt-2" />
                                    </div>

                                    <div x-show="isMale()" x-cloak>
                                        <label for="suit_size" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50">
                                                <img src="{{ $measurementIcons['male_suit'] }}" alt="" class="h-8 w-8 object-contain">
                                            </span>
                                            <span>Suit Size</span>
                                        </label>
                                        <x-text-input id="suit_size" name="suit_size" type="text" class="mt-1 block w-full" :value="old('suit_size', $profile->suit_size)" placeholder="Optional" />
                                        <x-input-error :messages="$errors->get('suit_size')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-500">Clothing & Appearance</p>
                                <div class="mt-4 grid gap-5 xl:grid-cols-2">
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-5">
                                        <div class="mb-4 flex items-start gap-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white">
                                                <img x-show="isFemale()" x-cloak src="{{ $measurementIcons['female_shoe'] }}" alt="" class="h-9 w-9 object-contain">
                                                <img x-show="!isFemale()" x-cloak src="{{ $measurementIcons['male_shoe'] }}" alt="" class="h-9 w-9 object-contain">
                                            </span>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900">Shoe Size</h4>
                                                <p class="mt-1 text-sm text-gray-500">Choose the sizing region and the actual size together.</p>
                                            </div>
                                        </div>
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <x-input-label for="shoe_size_region" :value="__('Region')" />
                                                <div class="relative mt-1" x-data="customSelect({
                                                    options: [{ value: '', label: 'Select region...' }, ...@js(collect($shoeSizeRegions)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all())],
                                                    selectedValue: shoeSizeRegion || ''
                                                })" x-init="init()" x-effect="syncFromExternal(shoeSizeRegion)">
                                                    <input type="hidden" id="shoe_size_region" name="shoe_size_region" x-model="selectedValue" />
                                                    <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                        <span x-text="selectedLabel || 'Select region...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                                    </button>
                                                    <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                        <template x-for="(option, index) in options" :key="index">
                                                            <button type="button" @click="selectOption(option.value); shoeSizeRegion = option.value; validateSelectedShoeSize()" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                                <span x-text="option.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <x-input-error :messages="$errors->get('shoe_size_region')" class="mt-2" />
                                            </div>

                                            <div>
                                                <x-input-label for="shoe_size_value" :value="__('Size')" />
                                                <div class="relative mt-1" x-data="customSelect({
                                                    options: [],
                                                    selectedValue: shoeSizeValue || ''
                                                })" x-init="init()" x-effect="setOptions([{ value: '', label: 'Select size...' }, ...currentShoeSizes().map(size => ({ value: size, label: size }))]); syncFromExternal(shoeSizeValue)">
                                                    <input type="hidden" id="shoe_size_value" name="shoe_size_value" x-model="selectedValue" />
                                                    <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                        <span x-text="selectedLabel || 'Select size...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                                    </button>
                                                    <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                        <template x-for="(option, index) in options" :key="index">
                                                            <button type="button" @click="selectOption(option.value); shoeSizeValue = option.value" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                                <span x-text="option.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <x-input-error :messages="$errors->get('shoe_size_value')" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="isFemale()" x-cloak class="rounded-2xl border border-gray-200 bg-gray-50/60 p-5">
                                        <div class="mb-4 flex items-start gap-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white">
                                                <img src="{{ $measurementIcons['female_dress'] }}" alt="" class="h-9 w-9 object-contain">
                                            </span>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900">Dress Size</h4>
                                                <p class="mt-1 text-sm text-gray-500">Keep region and size together so the fit reads clearly at a glance.</p>
                                            </div>
                                        </div>
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <x-input-label for="dress_size_region" :value="__('Region')" />
                                                <div class="relative mt-1" x-data="customSelect({
                                                    options: [{ value: '', label: 'Select region...' }, ...@js(collect($dressSizeRegions)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all())],
                                                    selectedValue: dressSizeRegion || ''
                                                })" x-init="init()" x-effect="syncFromExternal(dressSizeRegion)">
                                                    <input type="hidden" id="dress_size_region" name="dress_size_region" x-model="selectedValue" />
                                                    <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                        <span x-text="selectedLabel || 'Select region...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                                    </button>
                                                    <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                        <template x-for="(option, index) in options" :key="index">
                                                            <button type="button" @click="selectOption(option.value); dressSizeRegion = option.value; validateSelectedDressSize()" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                                <span x-text="option.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <x-input-error :messages="$errors->get('dress_size_region')" class="mt-2" />
                                            </div>

                                            <div>
                                                <x-input-label for="dress_size_value" :value="__('Size')" />
                                                <div class="relative mt-1" x-data="customSelect({
                                                    options: [],
                                                    selectedValue: dressSizeValue || ''
                                                })" x-init="init()" x-effect="setOptions([{ value: '', label: 'Select size...' }, ...currentDressSizes().map(size => ({ value: size, label: size }))]); syncFromExternal(dressSizeValue)">
                                                    <input type="hidden" id="dress_size_value" name="dress_size_value" x-model="selectedValue" />
                                                    <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                        <span x-text="selectedLabel || 'Select size...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                                    </button>
                                                    <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                        <template x-for="(option, index) in options" :key="index">
                                                            <button type="button" @click="selectOption(option.value); dressSizeValue = option.value" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                                <span x-text="option.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <x-input-error :messages="$errors->get('dress_size_value')" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-5 xl:col-span-2">
                                        <div class="grid gap-5 md:grid-cols-2">
                                            <div>
                                                <label for="hair_color" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white">
                                                        <img x-show="isFemale()" x-cloak src="{{ $measurementIcons['female_hair'] }}" alt="" class="h-9 w-9 object-contain">
                                                        <img x-show="!isFemale()" x-cloak src="{{ $measurementIcons['male_hair'] }}" alt="" class="h-9 w-9 object-contain">
                                                    </span>
                                                    <span>Hair Colour</span>
                                                </label>
                                                <div class="relative mt-2" x-data="customSelect({
                                                    options: [{ value: '', label: 'Select hair colour...' }, ...@js(collect($hairColors)->map(fn ($option) => ['value' => $option, 'label' => $option])->values()->all())],
                                                    selectedValue: @js(old('hair_color', $profile->hair_color))
                                                })" x-init="init()">
                                                    <input type="hidden" id="hair_color" name="hair_color" x-model="selectedValue" />
                                                    <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                        <span x-text="selectedLabel || 'Select hair colour...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                                    </button>
                                                    <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                        <template x-for="(option, index) in options" :key="index">
                                                            <button type="button" @click="selectOption(option.value)" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                                <span x-text="option.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <x-input-error :messages="$errors->get('hair_color')" class="mt-2" />
                                            </div>

                                            <div>
                                                <label for="eye_color" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white">
                                                        <img src="{{ $measurementIcons['eyes'] }}" alt="" class="h-9 w-9 object-contain">
                                                    </span>
                                                    <span>Eye Colour</span>
                                                </label>
                                                <div class="relative mt-2" x-data="customSelect({
                                                    options: [{ value: '', label: 'Select eye colour...' }, ...@js(collect($eyeColors)->map(fn ($option) => ['value' => $option, 'label' => $option])->values()->all())],
                                                    selectedValue: @js(old('eye_color', $profile->eye_color))
                                                })" x-init="init()">
                                                    <input type="hidden" id="eye_color" name="eye_color" x-model="selectedValue" />
                                                    <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                                        <span x-text="selectedLabel || 'Select eye colour...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                                    </button>
                                                    <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                                        <template x-for="(option, index) in options" :key="index">
                                                            <button type="button" @click="selectOption(option.value)" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                                <span x-text="option.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <x-input-error :messages="$errors->get('eye_color')" class="mt-2" />
                                            </div>
                                        </div>
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
                            <p class="mt-2 text-sm leading-6 text-gray-600">Help photographers quickly understand your background and strengths.</p>
                        </div>

                        <div class="space-y-6">
                            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                <div>
                                    <x-input-label for="experience_level" :value="__('Experience Level')" />
                                    <div class="relative mt-1" x-data="customSelect({
                                        options: [
                                            { value: '', label: 'Select level...' },
                                            { value: 'beginner', label: 'Beginner' },
                                            { value: 'intermediate', label: 'Intermediate' },
                                            { value: 'professional', label: 'Professional' }
                                        ],
                                        selectedValue: @js(old('experience_level', $profile->experience_level))
                                    })" x-init="init()">
                                        <input type="hidden" id="experience_level" name="experience_level" x-model="selectedValue" />
                                        <button type="button" @click="showDropdown = !showDropdown" @click.outside="showDropdown = false" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-400 focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                                            <span x-text="selectedLabel || 'Select level...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                        </button>
                                        <div x-show="showDropdown" x-cloak x-transition x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-xl">
                                            <template x-for="(option, index) in options" :key="index">
                                                <button type="button" @click="selectOption(option.value)" class="block w-full border-b border-gray-100 px-4 py-2 text-left last:border-b-0 hover:bg-gray-50" :class="selectedValue === option.value ? 'bg-gray-100 text-black' : 'text-gray-700'">
                                                    <span x-text="option.label"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="experience_start_year" :value="__('Started Modelling In')" />
                                    <x-text-input id="experience_start_year" name="experience_start_year" type="number" min="1900" max="{{ date('Y') }}" class="mt-1 block w-full" :value="old('experience_start_year', $profile->experience_start_year)" />
                                    <x-input-error :messages="$errors->get('experience_start_year')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <x-input-label :value="__('Specialties')" />
                                <p class="mt-2 text-sm text-gray-500">Choose the areas that best reflect the work you want to be known for.</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                    @foreach($specialtiesOptions as $key => $label)
                                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                                            <input type="checkbox" name="specialties[]" value="{{ $key }}" @checked(in_array($key, old('specialties', $profile->specialties ?? []), true)) class="rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('specialties')" class="mt-2" />
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
                            <p class="mt-2 text-sm leading-6 text-gray-600">Keep your contact handling tidy while still giving people the right paths to reach you.</p>
                        </div>

                        <div class="space-y-6">
                            <div class="grid gap-5 xl:grid-cols-3">
                                <div class="xl:col-span-2">
                                    <x-input-label for="public_email" :value="__('Professional Contact Email')" />
                                    <x-text-input id="public_email" name="public_email" type="email" class="mt-1 block w-full" :value="old('public_email', $profile->public_email ?: $user->email)" />
                                    <p class="mt-2 text-xs text-gray-500">This email receives message notifications and copies. It is not shown publicly on the profile page.</p>
                                    <x-input-error :messages="$errors->get('public_email')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <x-input-label :value="__('Social Links')" />
                                        <p class="mt-2 text-sm text-gray-500">Add whichever public platforms you actually want shown on your profile.</p>
                                    </div>
                                    <button type="button" @click="addSocialLink()" class="inline-flex items-center rounded-md border border-black px-3 py-2 text-sm font-medium text-black transition hover:bg-gray-100">
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
                                                <input type="url" x-model="link.url" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black" :placeholder="socialPlaceholder(link.platform)">
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
                        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <div class="mb-6">
                            <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                                <i class="fas fa-eye text-gray-400"></i>
                                <span>Visibility</span>
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">Decide how your profile appears to visitors and how explicit content is handled.</p>
                        </div>

                        <div class="grid gap-4 xl:grid-cols-2">
                            <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-4 py-4">
                                <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $profile->is_public ?? true)) class="mt-1 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                <span>
                                    <span class="block text-sm font-medium text-gray-900">Public profile</span>
                                    <span class="block text-sm text-gray-500">Allow visitors and members to view your profile page.</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-3 rounded-xl border border-gray-200 px-4 py-4">
                                <input type="checkbox" name="contains_nudity" value="1" @checked(old('contains_nudity', $profile->contains_nudity)) class="mt-1 rounded border-gray-300 text-black shadow-sm focus:ring-black">
                                <span>
                                    <span class="block text-sm font-medium text-gray-900">Portfolio contains nudity</span>
                                    <span class="block text-sm text-gray-500">Used for visibility warnings and age-gated presentation where needed.</span>
                                </span>
                            </label>
                        </div>
                        </div>
                    </section>

                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route('models.show', $user->id) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Cancel
                        </a>
                        <x-primary-button>
                            Save Profile
                        </x-primary-button>
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
                const selected = this.options.find((opt) => opt.value === this.selectedValue);
                this.selectedLabel = selected ? selected.label : '';
            },

            setOptions(options) {
                this.options = options || [];
                this.syncSelectedLabel();
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

                if (config.onSelect) {
                    config.onSelect(value);
                }
            },
        };
    }

    function searchableDropdown(config = {}) {
        return {
            countries: [],
            filteredCountries: [],
            searchInput: '',
            selectedValue: '',
            selectedLabel: '',
            showDropdown: false,
            highlightedIndex: -1,

            initCountries(countriesList, selectedCode) {
                if (!countriesList || typeof countriesList !== 'object' || Object.keys(countriesList).length === 0) {
                    return;
                }

                this.countries = Object.keys(countriesList).map((code) => ({
                    code,
                    name: countriesList[code],
                })).sort((a, b) => a.name.localeCompare(b.name));

                this.filteredCountries = this.countries.slice(0, 50);

                if (selectedCode) {
                    const selected = this.countries.find((country) => country.code === selectedCode);
                    if (selected) {
                        this.selectedValue = selected.code;
                        this.selectedLabel = selected.name;
                        this.searchInput = selected.name;
                    }
                }
            },

            filterCountries() {
                if (!this.countries.length) {
                    return;
                }

                const search = this.searchInput.toLowerCase().trim();
                this.filteredCountries = !search
                    ? this.countries.slice(0, 50)
                    : this.countries.filter((country) => country.name.toLowerCase().includes(search) || country.code.toLowerCase().includes(search));
                this.highlightedIndex = -1;
            },

            selectCountry(country) {
                this.selectedValue = country.code;
                this.selectedLabel = country.name;
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
                if (this.highlightedIndex !== -1 && this.filteredCountries[this.highlightedIndex]) {
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
        const viewportHeight = window.innerHeight;
        const spaceBelow = viewportHeight - rect.bottom;
        const spaceAbove = rect.top;

        if (spaceBelow < 200 && spaceAbove > spaceBelow) {
            dropdown.classList.add('bottom-full');
            dropdown.classList.remove('mt-1');
            dropdown.classList.add('mb-1');
            dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
            return;
        }

        dropdown.classList.remove('bottom-full', 'mb-1');
        dropdown.classList.add('mt-1');
        dropdown.style.maxHeight = Math.min(Math.max(spaceBelow - 20, 120), 240) + 'px';
    };

    function modelProfileEditor() {
        return {
            selectedCountry: @json(old('location_country_code', $profile->location_country_code ?? '')),
            cityInput: @json(old('location_city', $profile->location_city ?? '')),
            selectedGeonameId: @json(old('location_geoname_id', $profile->location_geoname_id ?? null)),
            selectedCountryName: @json(old('location_country', $profile->location_country ?? '')),
            displayNameFormat: @json($initialDisplayNameFormat),
            displayNameFormatOptions: @json($displayNameFormatOptions),
            displayNameDropdownOpen: false,
            suggestions: [],
            showSuggestions: false,
            searchTimeout: null,
            gender: @json(old('gender', $profile->gender ?? '')),
            measurementSystem: @json($initialMeasurementSystem),
            measurementDefaults: @json(\App\Helpers\ModelProfileOptions::measurementSystemCountryDefaults()),
            heightCm: @json($initialHeightCm),
            chestCm: @json($initialChestCm),
            waistCm: @json($initialWaistCm),
            inseamCm: @json($initialInseamCm),
            bustCm: @json($initialBustCm),
            hipsCm: @json($initialHipsCm),
            heightDisplay: '',
            heightFeet: '',
            heightInches: '',
            chestDisplay: '',
            waistDisplay: '',
            inseamDisplay: '',
            bustDisplay: '',
            hipsDisplay: '',
            shoeSizeRegion: @json($initialShoeRegion),
            shoeSizeValue: @json($initialShoeValue),
            shoeSizes: @json($shoeSizes),
            dressSizeRegion: @json($initialDressRegion),
            dressSizeValue: @json($initialDressValue),
            dressSizes: @json($dressSizes),
            socialLinks: @json($initialSocialLinks),
            countries: @json($countries),
            toast: {
                show: false,
                message: @json(session('status')),
            },

            init() {
                if (!this.selectedCountryName && this.selectedCountry) {
                    this.selectedCountryName = this.countries[this.selectedCountry] || '';
                }

                this.applyDefaultMeasurementSystem();
                this.applyDefaultSizeRegions();
                this.syncMeasurementDisplaysFromCanonical();
                this.validateSelectedShoeSize();
                this.validateSelectedDressSize();

                if (this.toast.message) {
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3200);
                }
            },

            normalizedGender() {
                return String(this.gender || '').trim().toLowerCase();
            },

            isMale() {
                return this.normalizedGender() === 'male';
            },

            isFemale() {
                return this.normalizedGender() === 'female';
            },

            getCountryDefaultMeasurementSystem(countryCode) {
                return this.measurementDefaults[countryCode] || 'metric';
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

            usesMetricHeight() {
                return ['metric', 'mixed_metric_default'].includes(this.measurementSystem);
            },

            usesMetricBodyMeasurements() {
                return ['metric', 'mixed_metric_default'].includes(this.measurementSystem);
            },

            bodyMeasurementLabel(label) {
                return `${label} (${this.usesMetricBodyMeasurements() ? 'cm' : 'in'})`;
            },

            roundToSingleDecimal(value) {
                return Math.round(value * 10) / 10;
            },

            toNullableInteger(value) {
                const numeric = Number(value);
                return !value || Number.isNaN(numeric) ? '' : Math.round(numeric);
            },

            toNullableDecimal(value) {
                const numeric = Number(value);
                return !value || Number.isNaN(numeric) ? '' : this.roundToSingleDecimal(numeric);
            },

            convertToMetric(value, multiplier) {
                const numeric = Number(value);
                if (!value || Number.isNaN(numeric)) {
                    return '';
                }

                return this.roundToSingleDecimal(numeric * multiplier);
            },

            syncMeasurementDisplaysFromCanonical() {
                const heightCm = Number(this.heightCm || 0);

                if (this.usesMetricHeight()) {
                    this.heightDisplay = heightCm || '';
                    this.heightFeet = '';
                    this.heightInches = '';
                } else if (heightCm) {
                    const totalInches = Math.round(heightCm / 2.54);
                    this.heightFeet = Math.floor(totalInches / 12);
                    this.heightInches = totalInches % 12;
                    this.heightDisplay = '';
                } else {
                    this.heightDisplay = '';
                    this.heightFeet = '';
                    this.heightInches = '';
                }

                if (this.usesMetricBodyMeasurements()) {
                    this.chestDisplay = this.chestCm || '';
                    this.waistDisplay = this.waistCm || '';
                    this.inseamDisplay = this.inseamCm || '';
                    this.bustDisplay = this.bustCm || '';
                    this.hipsDisplay = this.hipsCm || '';
                } else {
                    this.chestDisplay = this.chestCm ? this.roundToSingleDecimal(Number(this.chestCm) / 2.54) : '';
                    this.waistDisplay = this.waistCm ? this.roundToSingleDecimal(Number(this.waistCm) / 2.54) : '';
                    this.inseamDisplay = this.inseamCm ? this.roundToSingleDecimal(Number(this.inseamCm) / 2.54) : '';
                    this.bustDisplay = this.bustCm ? this.roundToSingleDecimal(Number(this.bustCm) / 2.54) : '';
                    this.hipsDisplay = this.hipsCm ? this.roundToSingleDecimal(Number(this.hipsCm) / 2.54) : '';
                }
            },

            updateCanonicalMeasurements() {
                if (this.usesMetricHeight()) {
                    this.heightCm = this.toNullableInteger(this.heightDisplay);
                } else {
                    const feet = Number(this.heightFeet || 0);
                    const inches = Number(this.heightInches || 0);
                    this.heightCm = feet || inches ? Math.round(((feet * 12) + inches) * 2.54) : '';
                }

                if (this.usesMetricBodyMeasurements()) {
                    this.chestCm = this.toNullableDecimal(this.chestDisplay);
                    this.waistCm = this.toNullableDecimal(this.waistDisplay);
                    this.inseamCm = this.toNullableDecimal(this.inseamDisplay);
                    this.bustCm = this.toNullableDecimal(this.bustDisplay);
                    this.hipsCm = this.toNullableDecimal(this.hipsDisplay);
                    return;
                }

                this.chestCm = this.convertToMetric(this.chestDisplay, 2.54);
                this.waistCm = this.convertToMetric(this.waistDisplay, 2.54);
                this.inseamCm = this.convertToMetric(this.inseamDisplay, 2.54);
                this.bustCm = this.convertToMetric(this.bustDisplay, 2.54);
                this.hipsCm = this.convertToMetric(this.hipsDisplay, 2.54);
            },

            changeMeasurementSystem(nextSystem) {
                this.updateCanonicalMeasurements();
                this.measurementSystem = nextSystem || 'metric';
                this.syncMeasurementDisplaysFromCanonical();
            },

            applyDefaultMeasurementSystem() {
                if (!this.selectedCountry) {
                    return;
                }

                if (!this.measurementSystem || this.measurementSystem === 'imperial') {
                    this.measurementSystem = this.getCountryDefaultMeasurementSystem(this.selectedCountry);
                }
            },

            getDefaultShoeRegionForCountry(countryCode) {
                if (!countryCode) {
                    return 'eu';
                }

                if (countryCode === 'GB' || countryCode === 'IE') {
                    return 'uk';
                }

                if (countryCode === 'US' || countryCode === 'CA') {
                    return this.isMale() ? 'us_men' : 'us_women';
                }

                return 'eu';
            },

            getDefaultDressRegionForCountry(countryCode) {
                if (!countryCode) {
                    return 'eu';
                }

                if (countryCode === 'GB' || countryCode === 'IE') {
                    return 'uk';
                }

                if (countryCode === 'US' || countryCode === 'CA') {
                    return 'us';
                }

                return 'eu';
            },

            applyDefaultSizeRegions() {
                if (!this.shoeSizeRegion) {
                    this.shoeSizeRegion = this.getDefaultShoeRegionForCountry(this.selectedCountry);
                }

                if (this.isFemale() && !this.dressSizeRegion) {
                    this.dressSizeRegion = this.getDefaultDressRegionForCountry(this.selectedCountry);
                }

                if (!this.isFemale()) {
                    this.dressSizeRegion = '';
                    this.dressSizeValue = '';
                }

                this.validateSelectedShoeSize();
                this.validateSelectedDressSize();
            },

            handleCountryChange() {
                this.selectedCountryName = this.countries[this.selectedCountry] || '';
                this.selectedGeonameId = null;
                this.cityInput = '';
                this.suggestions = [];
                this.showSuggestions = false;
                this.applyDefaultMeasurementSystem();
                this.applyDefaultSizeRegions();
                this.validateSelectedShoeSize();
                this.validateSelectedDressSize();
            },

            currentShoeSizes() {
                return this.shoeSizes[this.shoeSizeRegion] || [];
            },

            currentDressSizes() {
                return this.dressSizes[this.dressSizeRegion] || [];
            },

            validateSelectedShoeSize() {
                if (this.shoeSizeValue && !this.currentShoeSizes().includes(this.shoeSizeValue)) {
                    this.shoeSizeValue = '';
                }
            },

            validateSelectedDressSize() {
                if (!this.isFemale()) {
                    this.dressSizeValue = '';
                    return;
                }

                if (this.dressSizeValue && !this.currentDressSizes().includes(this.dressSizeValue)) {
                    this.dressSizeValue = '';
                }
            },

            async searchCities() {
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

            socialPlatformOptions() {
                return [
                    { value: 'instagram', label: 'Instagram' },
                    { value: 'facebook', label: 'Facebook' },
                    { value: 'x', label: 'X' },
                    { value: 'tiktok', label: 'TikTok' },
                    { value: 'youtube', label: 'YouTube' },
                    { value: 'behance', label: 'Behance' },
                    { value: 'linkedin', label: 'LinkedIn' },
                    { value: 'website', label: 'Website / Portfolio' },
                ];
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
        };
    }
</script>
