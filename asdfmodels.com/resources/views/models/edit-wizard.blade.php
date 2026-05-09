<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Your Model Profile') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="modelProfileWizard()" x-init="init()" @location-updated.window="handleLocationUpdate($event.detail)">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="flex items-center flex-1">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300"
                                             :class="currentStep > index ? 'bg-black border-black text-white' : (currentStep === index ? 'border-black bg-white text-black' : 'border-gray-300 bg-white text-gray-400')">
                                            <span x-show="currentStep > index"><i class="fas fa-check text-sm"></i></span>
                                            <span x-show="currentStep <= index" x-text="index + 1" class="font-semibold"></span>
                                        </div>
                                        <div class="ml-3 hidden md:block">
                                            <div class="text-sm font-medium" 
                                                 :class="currentStep >= index ? 'text-black' : 'text-gray-400'"
                                                 x-text="step.title"></div>
                                        </div>
                                    </div>
                                    <div x-show="index < steps.length - 1" class="flex-1 mx-4 h-0.5 transition-all duration-300"
                                         :class="currentStep > index ? 'bg-black' : 'bg-gray-300'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="text-center md:hidden">
                    <div class="text-sm font-medium text-black" x-text="steps[currentStep].title"></div>
                    <div class="text-xs text-gray-500 mt-1">Step <span x-text="currentStep + 1"></span> of <span x-text="steps.length"></span></div>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded border-2 border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Wizard Form -->
            <form method="POST" action="{{ route('profile.model.update') }}" @submit.prevent="saveProfile()" id="profileForm">
                @csrf
                @method('patch')

                @php
                    $countriesData = config('countries');
                    $measurementSystems = $measurementSystems ?? \App\Helpers\ModelProfileOptions::measurementSystems();
                    $measurementSystemCountryDefaults = \App\Helpers\ModelProfileOptions::measurementSystemCountryDefaults();
                    $displayNameFormats = \App\Helpers\ModelProfileOptions::displayNameFormatOptionsForNames(
                        $user->first_name ?? '',
                        $user->last_name ?? '',
                        $profile?->isVerified() ?? false
                    );
                    $shoeSizeRegions = $shoeSizeRegions ?? \App\Helpers\ModelProfileOptions::shoeSizeRegions();
                    $shoeSizes = $shoeSizes ?? \App\Helpers\ModelProfileOptions::shoeSizes();
                    $dressSizeRegions = $dressSizeRegions ?? \App\Helpers\ModelProfileOptions::dressSizeRegions();
                    $dressSizes = $dressSizes ?? \App\Helpers\ModelProfileOptions::dressSizes();
                    $hairColors = $hairColors ?? \App\Helpers\ModelProfileOptions::hairColors();
                    $eyeColors = $eyeColors ?? \App\Helpers\ModelProfileOptions::eyeColors();
                    $initialSocialLinks = collect($profile->social_links ?? [])->values()->map(function ($link, $index) {
                        return [
                            'uid' => 'existing-' . $index,
                            'platform' => $link['platform'] ?? '',
                            'url' => $link['url'] ?? '',
                        ];
                    })->all();
                @endphp

                <!-- Step 1: Basic Information -->
                <div x-show="currentStep === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Tell Us About Yourself</h3>
                        <p class="text-gray-600">Let's start with the basics</p>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="first_name" :value="__('First Name')" />
                                <x-text-input id="first_name" name="first_name" type="text" x-model="formData.first_name" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 text-gray-900 placeholder-gray-400" placeholder="First name" autocomplete="given-name" />
                                <p class="mt-1 text-xs text-gray-500">This is the public-facing first name used across the platform.</p>
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="last_name" :value="__('Last Name')" />
                                <x-text-input id="last_name" name="last_name" type="text" x-model="formData.last_name" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 text-gray-900 placeholder-gray-400" placeholder="Last name" autocomplete="family-name" />
                                <p class="mt-1 text-xs text-gray-500">The last name is stored separately so it can be hidden from public display.</p>
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="display_name_format" :value="__('Display Name As')" />
                            <div class="relative mt-1" @click.outside="displayNameDropdownOpen = false">
                                <input type="hidden" name="display_name_format" x-model="formData.display_name_format" />
                                <button type="button" @click="displayNameDropdownOpen = !displayNameDropdownOpen" class="flex w-full items-center justify-between rounded-md border-2 border-gray-800 bg-white px-3 py-2 text-left shadow-sm transition hover:border-gray-700 focus:border-black focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <span x-text="displayNameFormatLabel()" :class="formData.display_name_format ? 'text-gray-900' : 'text-gray-400'"></span>
                                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                </button>
                                <div x-show="displayNameDropdownOpen" x-cloak x-transition x-init="$watch('displayNameDropdownOpen', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });" class="absolute z-50 w-full overflow-y-auto rounded-md border-2 border-gray-800 bg-white shadow-xl">
                                    <template x-for="(option, index) in displayNameFormats" :key="index">
                                        <button
                                            type="button"
                                            @mousedown.prevent="selectDisplayNameFormat(option.value)"
                                            @click.prevent
                                            class="flex w-full cursor-pointer items-start gap-3 border-b border-gray-200 px-4 py-2.5 text-left last:border-b-0 hover:bg-gray-50"
                                            :class="formData.display_name_format === option.value ? 'bg-gray-800 text-white' : option.locked ? 'bg-gray-50 text-gray-400' : 'text-gray-900'"
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
                            <p class="mt-1 text-xs text-gray-500">The full-name option stays visible for clarity, but only verified members can actually select it.</p>
                            <x-input-error :messages="$errors->get('display_name_format')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="bio" :value="__('Bio')" />
                            <x-textarea
                                id="bio"
                                name="bio"
                                rows="4"
                                x-model="formData.bio"
                                class="block mt-1 w-full"
                                placeholder="Tell us about yourself, your experience, and what makes you unique..."
                            ></x-textarea>
                            <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="locationAutocomplete()" x-init="init(formData.locationCountryCode || '', formData.locationCity || '', formData.locationGeonameId || null)">
                            <div>
                                <x-input-label for="location_country_code" :value="__('Country')" />
                                <div class="relative mt-1" 
                                     x-data="searchableDropdown()" 
                                     x-init="initCountries(@js($countriesData), formData.locationCountryCode || '')">
                                    <div class="relative">
                                        <x-text-input 
                                            id="location_country_code" 
                                            type="text" 
                                            x-model="searchInput"
                                            @input="filterCountries()"
                                            @focus="showDropdown = true; if(filteredCountries.length === 0 && countries.length > 0) { filteredCountries = countries.slice(0, 50); }"
                                            @blur="setTimeout(() => showDropdown = false, 200)"
                                            @keydown.arrow-down.prevent="highlightNext()"
                                            @keydown.arrow-up.prevent="highlightPrevious()"
                                            @keydown.enter.prevent="selectHighlighted()"
                                            @keydown.escape="showDropdown = false"
                                            class="block w-full pr-10" 
                                            placeholder="Type to search countries..." 
                                            autocomplete="off" />
                                        <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                            <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="location_country_code" x-model="selectedValue" />
                                    <div x-show="showDropdown && filteredCountries.length > 0" 
                                         x-cloak
                                         x-transition
                                         x-init="
                                            $watch('showDropdown', value => {
                                                if (value) {
                                                    setTimeout(() => {
                                                        window.positionFloatingDropdown($el);
                                                    }, 10);
                                                }
                                            });
                                         "
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                         style="max-height: 240px;">
                                        <template x-for="(country, index) in filteredCountries" :key="country.code">
                                            <div @click="selectCountry(country); $dispatch('location-updated', {country: country.code})" 
                                                 @mouseenter="highlightedIndex = index"
                                                 :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === country.code, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== country.code }"
                                                 class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                <div class="font-medium" x-text="country.name"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="showDropdown && filteredCountries.length === 0" 
                                         x-cloak
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl p-4 text-center text-gray-500">
                                        No countries found
                                    </div>
                                </div>
                                <x-input-error :messages="$errors?->get('location_country_code') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="location_city" :value="__('City')" />
                                <div class="relative">
                                    <x-text-input 
                                        id="location_city" 
                                        name="location_city" 
                                        type="text" 
                                        x-model="cityInput"
                                        @input="searchCities()"
                                        @focus="showSuggestions = true"
                                        @blur="setTimeout(() => showSuggestions = false, 200)"
                                        class="block mt-1 w-full" 
                                        placeholder="Start typing city name..." 
                                        autocomplete="off" />
                                    <input type="hidden" name="location_geoname_id" x-model="selectedGeonameId" />
                                    <input type="hidden" name="location_country" x-model="selectedCountryName" />
                                    
                                    <div x-show="showSuggestions && suggestions.length > 0" 
                                         x-cloak
                                         x-init="
                                            $watch('showSuggestions', value => {
                                                if (value) {
                                                    setTimeout(() => {
                                                        window.positionFloatingDropdown($el);
                                                    }, 10);
                                                }
                                            });
                                         "
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                         style="max-height: 240px;">
                                        <template x-for="(suggestion, index) in suggestions" :key="index">
                                            <div @click="selectCity(suggestion); $dispatch('location-updated', {city: suggestion.city, geonameId: suggestion.id})" 
                                                 class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                <div class="font-medium text-black" x-text="suggestion.city"></div>
                                                <div class="text-sm text-gray-600" x-text="suggestion.label"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('location_city')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="gender" :value="__('Gender')" />
                                <div class="relative mt-1" x-data="customSelect({
                                    options: [
                                        { value: '', label: 'Select...' },
                                        { value: 'male', label: 'Male' },
                                        { value: 'female', label: 'Female' },
                                        { value: 'other', label: 'Other' }
                                    ],
                                    selectedValue: formData.gender || '',
                                    onSelect: (value) => { formData.gender = value; updateGenderFields(); }
                                })" x-init="init()">
                                    <input type="hidden" name="gender" x-model="selectedValue" />
                                    <div @click="showDropdown = !showDropdown" 
                                         @click.outside="showDropdown = false"
                                         class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                        <span x-text="selectedLabel || 'Select...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                    </div>
                                    <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                        <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                    </div>
                                    <div x-show="showDropdown" 
                                         x-cloak
                                         x-transition
                                         x-init="
                                            $watch('showDropdown', value => {
                                                if (value) {
                                                    setTimeout(() => {
                                                        window.positionFloatingDropdown($el);
                                                    }, 10);
                                                }
                                            });
                                         "
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                         style="max-height: 240px;">
                                        <template x-for="(option, index) in options" :key="index">
                                            <div @click="selectOption(option.value)" 
                                                 @mouseenter="highlightedIndex = index"
                                                 :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                 class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                <div class="font-medium" x-text="option.label"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="experience_start_year" :value="__('What year did you start modeling?')" />
                                <x-text-input id="experience_start_year" name="experience_start_year" type="number" x-model="formData.experience_start_year" class="block mt-1 w-full" min="1900" max="{{ date('Y') }}" placeholder="e.g., 2020" />
                                <p class="mt-1 text-xs text-gray-500">Optional - helps show your experience level</p>
                                <x-input-error :messages="$errors->get('experience_start_year')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                            <x-text-input id="date_of_birth" name="date_of_birth" type="date" x-model="formData.date_of_birth" class="block mt-1 w-full" max="{{ date('Y-m-d') }}" />
                            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Step 2: Physical Stats -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Physical Stats</h3>
                        <p class="text-gray-600">Help photographers find the right fit for their projects</p>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-2xl border-2 border-gray-200 bg-gray-50 p-5">
                            <div class="mb-4">
                                <h4 class="text-lg font-semibold text-black">Measurement System</h4>
                                <p class="text-sm text-gray-600">We default this from the selected country, but you can override it any time.</p>
                            </div>
                            <div class="mb-4 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                                Suggested from your selected profile country:
                                <span class="font-semibold" x-text="measurementSystemLabels[getCountryDefaultMeasurementSystem(formData.locationCountryCode)] || 'Metric'"></span>.
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <template x-for="systemKey in availableMeasurementSystems()" :key="systemKey">
                                    <label class="flex items-start gap-3 rounded-xl border-2 px-4 py-3 cursor-pointer transition"
                                           :class="formData.measurement_system === systemKey ? 'border-black bg-white shadow-sm' : 'border-gray-300 bg-white hover:border-gray-500'">
                                        <input type="radio" name="measurement_system_choice" :value="systemKey" :checked="formData.measurement_system === systemKey" @change="setMeasurementSystem(systemKey)" class="mt-1 border-gray-400 text-black focus:ring-black">
                                        <span class="text-sm font-medium text-gray-800" x-text="measurementSystemLabels[systemKey]"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-2xl border-2 border-gray-200 p-5">
                            <div class="mb-4">
                                <h4 class="text-lg font-semibold text-black">Measurements</h4>
                                <p class="text-sm text-gray-600">Keep the core stats clean and standardized for the public profile.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div x-show="usesMetricHeight()">
                                    <x-input-label for="height_metric" :value="__('Height (cm)')" />
                                    <x-text-input id="height_metric" type="number" x-model="formData.height_display" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="100" max="250" step="1" placeholder="e.g. 175" />
                                    <p class="mt-1 text-xs text-gray-500">Example: 175 cm</p>
                                    <x-input-error :messages="$errors->get('height_cm')" class="mt-2" />
                                </div>
                                <div x-show="!usesMetricHeight()" class="grid grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label for="height_feet" :value="__('Height (ft)')" />
                                        <x-text-input id="height_feet" type="number" x-model="formData.height_feet" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="3" max="8" step="1" placeholder="e.g. 5" />
                                    </div>
                                    <div>
                                        <x-input-label for="height_inches" :value="__('Height (in)')" />
                                        <x-text-input id="height_inches" type="number" x-model="formData.height_inches" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="0" max="11" step="1" placeholder="e.g. 9" />
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-xs text-gray-500">Example: 5 ft 9 in</p>
                                        <x-input-error :messages="$errors->get('height_cm')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <h5 class="text-base font-semibold text-black">Body Measurements</h5>
                                <p class="mt-1 text-sm text-gray-600">Shown according to the profile gender where relevant.</p>
                            </div>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div x-show="isMale()">
                                    <label for="chest_display" class="block font-medium text-sm text-gray-700">
                                        <span x-text="bodyMeasurementLabel('Chest')"></span>
                                    </label>
                                    <x-text-input id="chest_display" type="number" x-model="formData.chest_display" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="20" max="200" step="0.1" ::placeholder="bodyMeasurementPlaceholder('Chest')" />
                                    <p class="mt-1 text-xs text-gray-500" x-text="bodyMeasurementExample('Chest')"></p>
                                    <x-input-error :messages="$errors->get('chest_cm')" class="mt-2" />
                                </div>
                                <div x-show="isFemale()">
                                    <label for="bust_display" class="block font-medium text-sm text-gray-700">
                                        <span x-text="bodyMeasurementLabel('Bust')"></span>
                                    </label>
                                    <x-text-input id="bust_display" type="number" x-model="formData.bust_display" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="20" max="200" step="0.1" ::placeholder="bodyMeasurementPlaceholder('Bust')" />
                                    <p class="mt-1 text-xs text-gray-500" x-text="bodyMeasurementExample('Bust')"></p>
                                    <x-input-error :messages="$errors->get('bust_cm')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="waist_display" class="block font-medium text-sm text-gray-700">
                                        <span x-text="bodyMeasurementLabel('Waist')"></span>
                                    </label>
                                    <x-text-input id="waist_display" type="number" x-model="formData.waist_display" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="20" max="200" step="0.1" ::placeholder="bodyMeasurementPlaceholder('Waist')" />
                                    <p class="mt-1 text-xs text-gray-500" x-text="bodyMeasurementExample('Waist')"></p>
                                    <x-input-error :messages="$errors->get('waist_cm')" class="mt-2" />
                                </div>

                                <div x-show="isMale()">
                                    <label for="inseam_display" class="block font-medium text-sm text-gray-700">
                                        <span x-text="bodyMeasurementLabel('Inseam')"></span>
                                    </label>
                                    <x-text-input id="inseam_display" type="number" x-model="formData.inseam_display" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="20" max="150" step="0.1" ::placeholder="bodyMeasurementPlaceholder('Inseam')" />
                                    <p class="mt-1 text-xs text-gray-500" x-text="bodyMeasurementExample('Inseam')"></p>
                                    <x-input-error :messages="$errors->get('inseam_cm')" class="mt-2" />
                                </div>

                                <div x-show="isFemale()">
                                    <label for="hips_display" class="block font-medium text-sm text-gray-700">
                                        <span x-text="bodyMeasurementLabel('Hips')"></span>
                                    </label>
                                    <x-text-input id="hips_display" type="number" x-model="formData.hips_display" @input="updateCanonicalMeasurements()" class="block mt-1 w-full" min="20" max="200" step="0.1" ::placeholder="bodyMeasurementPlaceholder('Hips')" />
                                    <p class="mt-1 text-xs text-gray-500" x-text="bodyMeasurementExample('Hips')"></p>
                                    <x-input-error :messages="$errors->get('hips_cm')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border-2 border-gray-200 p-5">
                            <div class="mb-4">
                                <h4 class="text-lg font-semibold text-black">Clothing & Appearance</h4>
                                <p class="text-sm text-gray-600">Sizing follows the selected country by default, with an override only if needed.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div x-show="isMale()">
                                    <x-input-label for="suit_size" :value="__('Suit Size')" />
                                    <x-text-input id="suit_size" name="suit_size" type="text" x-model="formData.suit_size" class="block mt-1 w-full" placeholder="e.g. 38R" />
                                    <x-input-error :messages="$errors->get('suit_size')" class="mt-2" />
                                </div>

                                <div x-show="isFemale()" class="rounded-xl border border-gray-200 p-4">
                                    <x-input-label for="dress_size_region" :value="__('Dress Size')" />
                                    <div class="mt-2 space-y-3">
                                        <div class="text-xs text-gray-500" x-show="!formData.dress_size_region_override">
                                            Using <span class="font-semibold" x-text="dressSizeRegions[formData.dress_size_region] || 'default region'"></span>
                                            based on the selected profile country.
                                        </div>
                                        <label class="flex items-center text-sm text-gray-700">
                                            <input type="checkbox" x-model="formData.dress_size_region_override" @change="applyLocationBasedSizeRegions()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ml-2">Use a different dress size region</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div x-show="formData.dress_size_region_override"
                                                 class="relative mt-1"
                                                 x-data="customSelect({
                                                    options: dressRegionOptions(),
                                                    selectedValue: formData.dress_size_region || '',
                                                    onSelect: (value) => {
                                                        formData.dress_size_region = value;
                                                        formData.dress_size_value = '';
                                                    }
                                                 })"
                                                 x-init="init()"
                                                 x-effect="setOptions(dressRegionOptions()); syncFromExternal(formData.dress_size_region || '')">
                                                <input type="hidden" name="dress_size_region_ui" x-model="selectedValue" />
                                                <div @click="showDropdown = !showDropdown"
                                                     @click.outside="showDropdown = false"
                                                     class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                                    <span x-text="selectedLabel || 'Region'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                </div>
                                                <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                                </div>
                                                <div x-show="showDropdown"
                                                     x-cloak
                                                     x-transition
                                                     x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                     style="max-height: 240px;">
                                                    <template x-for="(option, index) in options" :key="`dress-region-${option.value}`">
                                                        <div @click="selectOption(option.value)"
                                                             @mouseenter="highlightedIndex = index"
                                                             :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                             class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                            <div class="font-medium" x-text="option.label"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="relative mt-1" :class="formData.dress_size_region_override ? 'col-span-1' : 'col-span-2'"
                                                 x-data="customSelect({
                                                    options: dressSizeValueOptions(),
                                                    selectedValue: formData.dress_size_value || '',
                                                    onSelect: (value) => { formData.dress_size_value = value; }
                                                 })"
                                                 x-init="init()"
                                                 x-effect="setOptions(dressSizeValueOptions()); syncFromExternal(formData.dress_size_value || '')">
                                                <input type="hidden" name="dress_size_value_ui" x-model="selectedValue" />
                                                <div @click="showDropdown = !showDropdown"
                                                     @click.outside="showDropdown = false"
                                                     class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                                    <span x-text="selectedLabel || 'Size'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                </div>
                                                <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                                </div>
                                                <div x-show="showDropdown"
                                                     x-cloak
                                                     x-transition
                                                     x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                     style="max-height: 240px;">
                                                    <template x-for="(option, index) in options" :key="`dress-size-${option.value}`">
                                                        <div @click="selectOption(option.value)"
                                                             @mouseenter="highlightedIndex = index"
                                                             :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                             class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                            <div class="font-medium" x-text="option.label"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('dress_size_value')" class="mt-2" />
                                </div>

                                <div class="rounded-xl border border-gray-200 p-4">
                                    <x-input-label for="shoe_size_region" :value="__('Shoe Size')" />
                                    <div class="mt-2 space-y-3">
                                        <div class="text-xs text-gray-500" x-show="!formData.shoe_size_region_override">
                                            Using <span class="font-semibold" x-text="shoeSizeRegions[formData.shoe_size_region] || 'default region'"></span>
                                            based on the selected profile country.
                                        </div>
                                        <label class="flex items-center text-sm text-gray-700">
                                            <input type="checkbox" x-model="formData.shoe_size_region_override" @change="applyLocationBasedSizeRegions()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ml-2">Use a different shoe size region</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div x-show="formData.shoe_size_region_override"
                                                 class="relative mt-1"
                                                 x-data="customSelect({
                                                    options: shoeRegionOptions(),
                                                    selectedValue: formData.shoe_size_region || '',
                                                    onSelect: (value) => {
                                                        formData.shoe_size_region = value;
                                                        formData.shoe_size_value = '';
                                                    }
                                                 })"
                                                 x-init="init()"
                                                 x-effect="setOptions(shoeRegionOptions()); syncFromExternal(formData.shoe_size_region || '')">
                                                <input type="hidden" name="shoe_size_region_ui" x-model="selectedValue" />
                                                <div @click="showDropdown = !showDropdown"
                                                     @click.outside="showDropdown = false"
                                                     class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                                    <span x-text="selectedLabel || 'Region'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                </div>
                                                <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                                </div>
                                                <div x-show="showDropdown"
                                                     x-cloak
                                                     x-transition
                                                     x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                     style="max-height: 240px;">
                                                    <template x-for="(option, index) in options" :key="`shoe-region-${option.value}`">
                                                        <div @click="selectOption(option.value)"
                                                             @mouseenter="highlightedIndex = index"
                                                             :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                             class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                            <div class="font-medium" x-text="option.label"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="relative mt-1" :class="formData.shoe_size_region_override ? 'col-span-1' : 'col-span-2'"
                                                 x-data="customSelect({
                                                    options: shoeSizeValueOptions(),
                                                    selectedValue: formData.shoe_size_value || '',
                                                    onSelect: (value) => { formData.shoe_size_value = value; }
                                                 })"
                                                 x-init="init()"
                                                 x-effect="setOptions(shoeSizeValueOptions()); syncFromExternal(formData.shoe_size_value || '')">
                                                <input type="hidden" name="shoe_size_value_ui" x-model="selectedValue" />
                                                <div @click="showDropdown = !showDropdown"
                                                     @click.outside="showDropdown = false"
                                                     class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                                    <span x-text="selectedLabel || 'Size'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                </div>
                                                <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                                </div>
                                                <div x-show="showDropdown"
                                                     x-cloak
                                                     x-transition
                                                     x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                     style="max-height: 240px;">
                                                    <template x-for="(option, index) in options" :key="`shoe-size-${option.value}`">
                                                        <div @click="selectOption(option.value)"
                                                             @mouseenter="highlightedIndex = index"
                                                             :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                             class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                            <div class="font-medium" x-text="option.label"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('shoe_size_value')" class="mt-2" />
                                </div>

                                <div class="relative mt-1" x-data="customSelect({
                                    options: simpleOptions(hairColorValues),
                                    selectedValue: formData.hair_color || '',
                                    onSelect: (value) => { formData.hair_color = value; }
                                })" x-init="init()" x-effect="setOptions(simpleOptions(hairColorValues)); syncFromExternal(formData.hair_color || '')">
                                    <x-input-label for="hair_color" :value="__('Hair Colour')" />
                                    <input type="hidden" name="hair_color_ui" x-model="selectedValue" />
                                    <div @click="showDropdown = !showDropdown"
                                         @click.outside="showDropdown = false"
                                         class="block w-full mt-1 border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                        <span x-text="selectedLabel || 'Select...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                    </div>
                                    <div class="absolute right-0 flex items-center pointer-events-none" style="top: calc(50% + 14px); transform: translateY(-50%); right: 12px;">
                                        <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                    </div>
                                    <div x-show="showDropdown"
                                         x-cloak
                                         x-transition
                                         x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                         style="max-height: 240px;">
                                        <template x-for="(option, index) in options" :key="`hair-${option.value}`">
                                            <div @click="selectOption(option.value)"
                                                 @mouseenter="highlightedIndex = index"
                                                 :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                 class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                <div class="font-medium" x-text="option.label"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <x-input-error :messages="$errors->get('hair_color')" class="mt-2" />
                                </div>
                                <div class="relative mt-1" x-data="customSelect({
                                    options: simpleOptions(eyeColorValues),
                                    selectedValue: formData.eye_color || '',
                                    onSelect: (value) => { formData.eye_color = value; }
                                })" x-init="init()" x-effect="setOptions(simpleOptions(eyeColorValues)); syncFromExternal(formData.eye_color || '')">
                                    <x-input-label for="eye_color" :value="__('Eye Colour')" />
                                    <input type="hidden" name="eye_color_ui" x-model="selectedValue" />
                                    <div @click="showDropdown = !showDropdown"
                                         @click.outside="showDropdown = false"
                                         class="block w-full mt-1 border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                        <span x-text="selectedLabel || 'Select...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                    </div>
                                    <div class="absolute right-0 flex items-center pointer-events-none" style="top: calc(50% + 14px); transform: translateY(-50%); right: 12px;">
                                        <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                    </div>
                                    <div x-show="showDropdown"
                                         x-cloak
                                         x-transition
                                         x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                         style="max-height: 240px;">
                                        <template x-for="(option, index) in options" :key="`eye-${option.value}`">
                                            <div @click="selectOption(option.value)"
                                                 @mouseenter="highlightedIndex = index"
                                                 :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                 class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                <div class="font-medium" x-text="option.label"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <x-input-error :messages="$errors->get('eye_color')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Professional Information -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Professional Details</h3>
                        <p class="text-gray-600">Tell us about your modeling experience</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="experience_level" :value="__('Experience Level')" />
                            <div class="relative mt-1" x-data="customSelect({
                                options: [
                                    { value: '', label: 'Select...' },
                                    { value: 'beginner', label: 'Beginner' },
                                    { value: 'intermediate', label: 'Intermediate' },
                                    { value: 'professional', label: 'Professional' }
                                ],
                                selectedValue: formData.experience_level || '',
                                onSelect: (value) => { formData.experience_level = value; }
                            })" x-init="init()">
                                <input type="hidden" name="experience_level" x-model="selectedValue" />
                                <div @click="showDropdown = !showDropdown" 
                                     @click.outside="showDropdown = false"
                                     class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                    <span x-text="selectedLabel || 'Select...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                </div>
                                <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                </div>
                                <div x-show="showDropdown" 
                                     x-cloak
                                     x-transition
                                     x-init="
                                        $watch('showDropdown', value => {
                                            if (value) {
                                                setTimeout(() => {
                                                    window.positionFloatingDropdown($el);
                                                }, 10);
                                            }
                                        });
                                     "
                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                     style="max-height: 240px;">
                                    <template x-for="(option, index) in options" :key="index">
                                        <div @click="selectOption(option.value)" 
                                             @mouseenter="highlightedIndex = index"
                                             :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                             class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                            <div class="font-medium" x-text="option.label"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Specialties')" />
                            <p class="text-sm text-gray-600 mb-4">Select the types of modeling you specialize in</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @php
                                    $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties('model');
                                @endphp
                                @foreach($specialtiesOptions as $specialtyKey => $specialtyLabel)
                                    <label class="flex items-start cursor-pointer p-4 border-2 rounded-lg hover:border-black hover:shadow-md transition-all duration-200 group"
                                           :class="formData.specialties.includes('{{ $specialtyKey }}') ? 'border-black bg-black text-white shadow-lg' : 'border-gray-300 bg-white'">
                                        <input type="checkbox" 
                                               name="specialties[]" 
                                               value="{{ $specialtyKey }}"
                                               x-model="formData.specialties"
                                               class="mt-0.5 w-5 h-5 rounded border-2 border-gray-400 text-black focus:ring-2 focus:ring-black focus:ring-offset-2 cursor-pointer transition-all"
                                               :class="formData.specialties.includes('{{ $specialtyKey }}') ? 'border-white bg-white' : ''">
                                        <span class="ml-3 text-sm font-medium flex-1"
                                              :class="formData.specialties.includes('{{ $specialtyKey }}') ? 'text-white' : 'text-gray-700 group-hover:text-black'">{{ $specialtyLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors?->get('specialties') ?? []" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Step 4: Contact & Social -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Contact & Social Links</h3>
                        <p class="text-gray-600">Choose where member enquiries and profile link-outs should go.</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="public_email" :value="__('Professional Contact Email')" />
                            <x-text-input id="public_email" name="public_email" type="email" x-model="formData.public_email" class="block mt-1 w-full" placeholder="your@email.com" />
                            <p class="mt-1 text-xs text-gray-500">Messages from members will be sent through the website messaging system, and a copy will be sent to this email as a notification. Your actual email address will not be shown publicly.</p>
                            <x-input-error :messages="$errors->get('public_email')" class="mt-2" />
                        </div>

                        <div class="rounded-2xl border-2 border-gray-200 p-5">
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-black">Social Links</h4>
                                    <p class="text-sm text-gray-600">Add the platforms you want shown on your profile.</p>
                                </div>
                                <button type="button" @click="addSocialLink()" class="inline-flex items-center rounded-lg border-2 border-black px-4 py-2 text-sm font-semibold text-black hover:bg-black hover:text-white transition">
                                    <i class="fas fa-plus mr-2"></i> Add Link
                                </button>
                            </div>

                            <div class="space-y-4" x-show="formData.social_links.length > 0">
                                <template x-for="(link, index) in formData.social_links" :key="link.uid">
                                    <div class="rounded-xl border border-gray-200 p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)_auto] gap-4 items-start">
                                            <div class="relative mt-1"
                                                 x-data="customSelect({
                                                    options: socialPlatformOptions(),
                                                    selectedValue: link.platform || '',
                                                    onSelect: (value) => { formData.social_links[index].platform = value; }
                                                 })"
                                                 x-init="init()"
                                                 x-effect="setOptions(socialPlatformOptions()); syncFromExternal(link.platform || '')">
                                                <x-input-label :value="__('Platform')" />
                                                <input type="hidden" name="social_platform_ui" x-model="selectedValue" />
                                                <div @click="showDropdown = !showDropdown"
                                                     @click.outside="showDropdown = false"
                                                     class="block w-full mt-1 border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                                    <span x-text="selectedLabel || 'Select...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                                </div>
                                                <div class="absolute right-0 flex items-center pointer-events-none" style="top: calc(50% + 14px); transform: translateY(-50%); right: 12px;">
                                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                                </div>
                                                <div x-show="showDropdown"
                                                     x-cloak
                                                     x-transition
                                                     x-init="$watch('showDropdown', value => { if (value) { setTimeout(() => { window.positionFloatingDropdown($el); }, 10); } });"
                                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                     style="max-height: 240px;">
                                                    <template x-for="(option, optionIndex) in options" :key="`social-platform-${option.value}`">
                                                        <div @click="selectOption(option.value)"
                                                             @mouseenter="highlightedIndex = optionIndex"
                                                             :class="{ 'bg-gray-800 text-white': optionIndex === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': optionIndex !== highlightedIndex && selectedValue !== option.value }"
                                                             class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                            <div class="font-medium" x-text="option.label"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <div>
                                                <x-input-label :value="__('URL')" />
                                                <x-text-input type="url"
                                                    x-model="formData.social_links[index].url"
                                                    class="block mt-1 w-full"
                                                    ::placeholder="socialUrlPlaceholder(link.platform)" />
                                            </div>

                                            <div class="pt-7">
                                                <button type="button" @click="removeSocialLink(index)" class="inline-flex items-center rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 transition">
                                                    <i class="fas fa-trash mr-2"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="formData.social_links.length === 0" class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-sm text-gray-500 text-center">
                                No social links added yet.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Settings -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Almost Done!</h3>
                        <p class="text-gray-600">Final settings for your profile</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_public" value="1" x-model="formData.is_public" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Make profile public</span>
                            </label>
                            <p class="mt-2 ml-8 text-xs text-gray-500">When enabled, your profile will be visible to photographers and other users</p>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="contains_nudity" value="1" x-model="formData.contains_nudity" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Portfolio contains nudity</span>
                            </label>
                            <p class="mt-2 ml-8 text-xs text-gray-500">Check this if your portfolio includes artistic or fashion photography with nudity</p>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for all form data -->
                <input type="hidden" name="bio" x-model="formData.bio" />
                <input type="hidden" name="gender" x-model="formData.gender" />
                <input type="hidden" name="experience_start_year" x-model="formData.experience_start_year" />
                <input type="hidden" name="date_of_birth" x-model="formData.date_of_birth" />
                <input type="hidden" name="measurement_system" x-model="formData.measurement_system" />
                <input type="hidden" name="height_cm" x-model="formData.height_cm" />
                <input type="hidden" name="chest_cm" x-model="formData.chest_cm" />
                <input type="hidden" name="waist_cm" x-model="formData.waist_cm" />
                <input type="hidden" name="inseam_cm" x-model="formData.inseam_cm" />
                <input type="hidden" name="suit_size" x-model="formData.suit_size" />
                <input type="hidden" name="bust_cm" x-model="formData.bust_cm" />
                <input type="hidden" name="hips_cm" x-model="formData.hips_cm" />
                <input type="hidden" name="dress_size_region" x-model="formData.dress_size_region" />
                <input type="hidden" name="dress_size_value" x-model="formData.dress_size_value" />
                <input type="hidden" name="shoe_size_region" x-model="formData.shoe_size_region" />
                <input type="hidden" name="shoe_size_value" x-model="formData.shoe_size_value" />
                <input type="hidden" name="hair_color" x-model="formData.hair_color" />
                <input type="hidden" name="eye_color" x-model="formData.eye_color" />
                <input type="hidden" name="experience_level" x-model="formData.experience_level" />
                <template x-for="specialty in formData.specialties" :key="specialty">
                    <input type="hidden" name="specialties[]" :value="specialty" />
                </template>
                <input type="hidden" name="public_email" x-model="formData.public_email" />
                <template x-for="(link, index) in formData.social_links" :key="`social-hidden-${link.uid}`">
                    <div>
                        <input type="hidden" :name="`social_links[${index}][platform]`" :value="link.platform">
                        <input type="hidden" :name="`social_links[${index}][url]`" :value="link.url">
                    </div>
                </template>
                <input type="hidden" name="is_public" :value="formData.is_public ? 1 : 0" />
                <input type="hidden" name="contains_nudity" :value="formData.contains_nudity ? 1 : 0" />
            </form>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between items-center">
                <button type="button" 
                        @click="previousStep()" 
                        x-show="currentStep > 0"
                        class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
                <div class="flex-1"></div>
                <button type="button" 
                        @click="nextStep()" 
                        x-show="currentStep < steps.length - 1"
                        class="px-6 py-3 bg-black text-white rounded-lg font-semibold hover:bg-gray-800 transition">
                    Continue <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button type="button" 
                        @click="saveProfile()" 
                        x-show="currentStep === steps.length - 1"
                        class="px-6 py-3 bg-black text-white rounded-lg font-semibold hover:bg-gray-800 transition">
                    <i class="fas fa-check mr-2"></i> Complete Profile
                </button>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function modelProfileWizard() {
        return {
            currentStep: 0,
            steps: [
                { title: 'Basic Info', icon: 'user' },
                { title: 'Physical Stats', icon: 'ruler' },
                { title: 'Professional', icon: 'briefcase' },
                { title: 'Contact', icon: 'envelope' },
                { title: 'Settings', icon: 'cog' }
            ],
            formData: {
                bio: '',
                first_name: '',
                last_name: '',
                display_name_format: 'first_name_last_initial',
                locationCountryCode: '',
                locationCity: '',
                locationGeonameId: null,
                gender: '',
                measurement_system: 'metric',
                experience_start_year: '',
                date_of_birth: '',
                height_cm: '',
                weight_kg: '',
                chest_cm: '',
                waist_cm: '',
                inseam_cm: '',
                suit_size: '',
                bust_cm: '',
                hips_cm: '',
                dress_size_region: '',
                dress_size_value: '',
                dress_size_region_override: false,
                shoe_size_region: '',
                shoe_size_value: '',
                shoe_size_region_override: false,
                hair_color: '',
                eye_color: '',
                height_display: '',
                height_feet: '',
                height_inches: '',
                weight_display: '',
                weight_stone: '',
                weight_pounds: '',
                chest_display: '',
                waist_display: '',
                inseam_display: '',
                bust_display: '',
                hips_display: '',
                experience_level: '',
                specialties: [],
                public_email: '',
                instagram: '',
                portfolio_website: '',
                social_links: [],
                is_public: true,
                contains_nudity: false
            },
            measurementSystemLabels: @json($measurementSystems),
            measurementSystemCountryDefaults: @json($measurementSystemCountryDefaults),
            displayNameFormats: @json($displayNameFormats),
            displayNameDropdownOpen: false,
            shoeSizeRegions: @json($shoeSizeRegions),
            shoeSizes: @json($shoeSizes),
            dressSizeRegions: @json($dressSizeRegions),
            dressSizes: @json($dressSizes),
            hairColorValues: @json($hairColors),
            eyeColorValues: @json($eyeColors),
            init() {
                // Load existing profile data
                @if(isset($profile))
                    this.formData = {
                        bio: @json($profile->bio ?? ''),
                        first_name: @json($user->first_name ?? ''),
                        last_name: @json($user->last_name ?? ''),
                        display_name_format: @json(($profile->display_name_format && !($profile->display_name_format === 'full_name' && !($profile?->isVerified() ?? false))) ? $profile->display_name_format : (($profile?->isVerified() ?? false) ? 'full_name' : 'first_name_last_initial')),
                        locationCountryCode: @json($profile->location_country_code ?? ''),
                        locationCity: @json($profile->location_city ?? ''),
                        locationGeonameId: @json($profile->location_geoname_id ?? null),
                        gender: @json($profile->gender ?? ''),
                        measurement_system: @json(($profile->measurement_system ?? 'metric') === 'imperial' ? 'us_customary' : ($profile->measurement_system ?? 'metric')),
                        experience_start_year: @json($profile->experience_start_year ?? ''),
                        date_of_birth: @json($profile->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : ''),
                        height_cm: @json($profile->height_cm ?? ''),
                        weight_kg: @json($profile->weight_kg ?? ''),
                        chest_cm: @json($profile->chest_cm ?? ''),
                        waist_cm: @json($profile->waist_cm ?? ''),
                        inseam_cm: @json($profile->inseam_cm ?? ''),
                        suit_size: @json($profile->suit_size ?? ''),
                        bust_cm: @json($profile->bust_cm ?? ''),
                        hips_cm: @json($profile->hips_cm ?? ''),
                        dress_size_region: @json($profile->dress_size_region ?? ''),
                        dress_size_value: @json($profile->dress_size_value ?? ''),
                        dress_size_region_override: false,
                        shoe_size_region: @json($profile->shoe_size_region ?? ''),
                        shoe_size_value: @json($profile->shoe_size_value ?? ''),
                        shoe_size_region_override: false,
                        hair_color: @json($profile->hair_color ?? ''),
                        eye_color: @json($profile->eye_color ?? ''),
                        height_display: '',
                        height_feet: '',
                        height_inches: '',
                        weight_display: '',
                        weight_stone: '',
                        weight_pounds: '',
                        chest_display: '',
                        waist_display: '',
                        inseam_display: '',
                        bust_display: '',
                        hips_display: '',
                        experience_level: @json($profile->experience_level ?? ''),
                        specialties: @json($profile->specialties ?? []),
                        public_email: @json($profile->public_email ?? $user->email),
                        instagram: @json($profile->instagram ?? ''),
                        portfolio_website: @json($profile->portfolio_website ?? ''),
                        social_links: @json($initialSocialLinks),
                        is_public: @json($profile->is_public ?? true),
                        contains_nudity: @json($profile->contains_nudity ?? false)
                    };
                @endif
                if (!this.formData.public_email) {
                    this.formData.public_email = @json($user->email);
                }
                if (this.formData.social_links.length === 0) {
                    if (this.formData.instagram) {
                        this.formData.social_links.push({
                            uid: `legacy-instagram`,
                            platform: 'instagram',
                            url: this.normaliseLegacySocialUrl('instagram', this.formData.instagram),
                        });
                    }
                    if (this.formData.portfolio_website) {
                        this.formData.social_links.push({
                            uid: `legacy-website`,
                            platform: 'website',
                            url: this.formData.portfolio_website,
                        });
                    }
                }
                this.syncMeasurementDisplaysFromCanonical();
                this.initialiseSizeRegionOverrides();
                this.applyLocationBasedMeasurementSystem();
                this.applyLocationBasedSizeRegions();
            },
            displayNameFormatLabel() {
                const selected = this.displayNameFormats.find((option) => option.value === this.formData.display_name_format);
                return selected ? selected.label : 'Choose display format...';
            },
            selectDisplayNameFormat(value) {
                const selected = this.displayNameFormats.find((option) => option.value === value);
                if (selected && selected.locked) {
                    return;
                }

                this.formData.display_name_format = value;
                this.displayNameDropdownOpen = false;
            },
            nextStep() {
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                    // Auto-save disabled for now - will save on final submit
                }
            },
            previousStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                }
            },
            autoSave() {
                // Auto-save current step data (silent, don't show errors)
                const formData = new FormData();
                Object.keys(this.formData).forEach(key => {
                    if (Array.isArray(this.formData[key])) {
                        this.formData[key].forEach(item => {
                            formData.append(key + '[]', item);
                        });
                    } else {
                        formData.append(key, this.formData[key] || '');
                    }
                });
                formData.append('_method', 'PATCH');
                
                fetch('{{ route("profile.model.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                }).catch(() => {
                    // Silently fail - auto-save is optional
                });
            },
            saveProfile() {
                this.updateCanonicalMeasurements();
                document.getElementById('profileForm').submit();
            },
            updateGenderFields() {
                // Clear gender-specific fields when gender changes
                if (!this.isMale()) {
                    this.formData.chest_cm = '';
                    this.formData.chest_display = '';
                    this.formData.inseam_cm = '';
                    this.formData.inseam_display = '';
                    this.formData.suit_size = '';
                }
                if (!this.isFemale()) {
                    this.formData.bust_cm = '';
                    this.formData.bust_display = '';
                    this.formData.hips_cm = '';
                    this.formData.hips_display = '';
                    this.formData.dress_size_region = '';
                    this.formData.dress_size_value = '';
                    this.formData.dress_size_region_override = false;
                }

                this.applyLocationBasedSizeRegions();
            },
            handleLocationUpdate(detail) {
                if (detail.country) {
                    this.formData.locationCountryCode = detail.country;
                }

                if (detail.city) {
                    this.formData.locationCity = detail.city;
                }

                if (detail.geonameId) {
                    this.formData.locationGeonameId = detail.geonameId;
                }

                this.applyLocationBasedMeasurementSystem();
                this.applyLocationBasedSizeRegions();
            },
            normalizedGender() {
                return String(this.formData.gender || '').trim().toLowerCase();
            },
            isMale() {
                return this.normalizedGender() === 'male';
            },
            isFemale() {
                return this.normalizedGender() === 'female';
            },
            availableMeasurementSystems() {
                const defaultSystem = this.getCountryDefaultMeasurementSystem(this.formData.locationCountryCode);

                switch (defaultSystem) {
                    case 'us_customary':
                        return ['us_customary', 'metric'];
                    case 'mixed_uk':
                        return ['mixed_uk', 'metric'];
                    case 'mixed_ca':
                        return ['mixed_ca', 'metric'];
                    case 'mixed_metric_default':
                        return ['mixed_metric_default', 'us_customary'];
                    default:
                        return ['metric', 'us_customary'];
                }
            },
            getCountryDefaultMeasurementSystem(countryCode) {
                return this.measurementSystemCountryDefaults[countryCode] || 'metric';
            },
            applyLocationBasedMeasurementSystem() {
                const defaultSystem = this.getCountryDefaultMeasurementSystem(this.formData.locationCountryCode);
                if (defaultSystem && this.formData.measurement_system !== defaultSystem) {
                    this.formData.measurement_system = defaultSystem;
                    this.syncMeasurementDisplaysFromCanonical();
                }
            },
            setMeasurementSystem(systemKey) {
                this.formData.measurement_system = systemKey;
                this.syncMeasurementDisplaysFromCanonical();
            },
            initialiseSizeRegionOverrides() {
                const defaultShoeRegion = this.getDefaultShoeRegionForCountry(this.formData.locationCountryCode);
                const defaultDressRegion = this.getDefaultDressRegionForCountry(this.formData.locationCountryCode);

                this.formData.shoe_size_region_override = !!(this.formData.shoe_size_region && defaultShoeRegion && this.formData.shoe_size_region !== defaultShoeRegion);
                this.formData.dress_size_region_override = !!(this.formData.dress_size_region && defaultDressRegion && this.formData.dress_size_region !== defaultDressRegion);
            },
            applyLocationBasedSizeRegions() {
                if (!this.formData.shoe_size_region_override) {
                    const defaultShoeRegion = this.getDefaultShoeRegionForCountry(this.formData.locationCountryCode);
                    if (defaultShoeRegion) {
                        this.formData.shoe_size_region = defaultShoeRegion;
                    }
                }

                if (!this.formData.dress_size_region_override) {
                    const defaultDressRegion = this.getDefaultDressRegionForCountry(this.formData.locationCountryCode);
                    if (defaultDressRegion) {
                        this.formData.dress_size_region = defaultDressRegion;
                    }
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
            usesMetricHeight() {
                return ['metric', 'mixed_metric_default'].includes(this.formData.measurement_system);
            },
            usesMetricBodyMeasurements() {
                return ['metric', 'mixed_metric_default'].includes(this.formData.measurement_system);
            },
            usesStoneWeight() {
                return this.formData.measurement_system === 'mixed_uk';
            },
            usesPoundsWeight() {
                return ['us_customary', 'mixed_ca'].includes(this.formData.measurement_system);
            },
            weightLabel() {
                return this.usesPoundsWeight() ? 'Weight (lbs)' : 'Weight (kg)';
            },
            bodyMeasurementLabel(label) {
                return `${label} (${this.usesMetricBodyMeasurements() ? 'cm' : 'in'})`;
            },
            bodyMeasurementPlaceholder(label) {
                const metricExamples = {
                    Chest: '96',
                    Bust: '90',
                    Waist: '66',
                    Inseam: '81',
                    Hips: '94',
                };
                const imperialExamples = {
                    Chest: '38',
                    Bust: '35.5',
                    Waist: '26',
                    Inseam: '32',
                    Hips: '37',
                };

                return this.usesMetricBodyMeasurements()
                    ? (metricExamples[label] || '90')
                    : (imperialExamples[label] || '35');
            },
            bodyMeasurementExample(label) {
                return this.usesMetricBodyMeasurements()
                    ? `Example: ${this.bodyMeasurementPlaceholder(label)} ${label === 'Inseam' ? 'cm inseam' : 'cm'}`
                    : `Example: ${this.bodyMeasurementPlaceholder(label)} ${label === 'Inseam' ? 'in inseam' : 'in'}`;
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
            socialUrlPlaceholder(platform) {
                const placeholders = {
                    instagram: 'https://instagram.com/yourusername',
                    facebook: 'https://facebook.com/yourusername',
                    x: 'https://x.com/yourusername',
                    tiktok: 'https://tiktok.com/@yourusername',
                    youtube: 'https://youtube.com/@yourchannel',
                    behance: 'https://behance.net/yourname',
                    linkedin: 'https://linkedin.com/in/yourname',
                    website: 'https://yourportfolio.com',
                };
                return placeholders[platform] || 'https://example.com/your-profile';
            },
            normaliseLegacySocialUrl(platform, value) {
                if (!value) {
                    return '';
                }
                if (/^https?:\/\//i.test(value)) {
                    return value;
                }
                const clean = String(value).replace(/^@/, '');
                const prefixes = {
                    instagram: 'https://instagram.com/',
                    facebook: 'https://facebook.com/',
                    x: 'https://x.com/',
                };
                return (prefixes[platform] || '') + clean;
            },
            addSocialLink() {
                this.formData.social_links.push({
                    uid: `social-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                    platform: '',
                    url: '',
                });
            },
            removeSocialLink(index) {
                this.formData.social_links.splice(index, 1);
            },
            simpleOptions(values) {
                return (values || []).map((value) => ({ value, label: value }));
            },
            mappedOptions(values) {
                return Object.entries(values || {}).map(([value, label]) => ({ value, label }));
            },
            dressRegionOptions() {
                return this.mappedOptions(this.dressSizeRegions);
            },
            dressSizeValueOptions() {
                return this.simpleOptions(this.dressSizes[this.formData.dress_size_region] || []);
            },
            shoeRegionOptions() {
                return this.mappedOptions(this.shoeSizeRegions);
            },
            shoeSizeValueOptions() {
                return this.simpleOptions(this.shoeSizes[this.formData.shoe_size_region] || []);
            },
            syncMeasurementDisplaysFromCanonical() {
                const system = this.formData.measurement_system || 'metric';
                const heightCm = Number(this.formData.height_cm || 0);

                if (this.usesMetricHeight()) {
                    this.formData.height_display = heightCm || '';
                } else {
                    if (heightCm) {
                        const totalInches = Math.round(heightCm / 2.54);
                        this.formData.height_feet = Math.floor(totalInches / 12);
                        this.formData.height_inches = totalInches % 12;
                    } else {
                        this.formData.height_feet = '';
                        this.formData.height_inches = '';
                    }
                }

                if (this.usesStoneWeight()) {
                    if (this.formData.weight_kg) {
                        const totalPounds = Math.round(Number(this.formData.weight_kg) * 2.20462);
                        this.formData.weight_stone = Math.floor(totalPounds / 14);
                        this.formData.weight_pounds = totalPounds % 14;
                    } else {
                        this.formData.weight_stone = '';
                        this.formData.weight_pounds = '';
                    }
                    this.formData.weight_display = '';
                } else if (this.usesPoundsWeight()) {
                    this.formData.weight_display = this.formData.weight_kg ? this.roundToSingleDecimal(Number(this.formData.weight_kg) * 2.20462) : '';
                    this.formData.weight_stone = '';
                    this.formData.weight_pounds = '';
                } else {
                    this.formData.weight_display = this.formData.weight_kg || '';
                    this.formData.weight_stone = '';
                    this.formData.weight_pounds = '';
                }

                if (this.usesMetricBodyMeasurements()) {
                    this.formData.chest_display = this.formData.chest_cm || '';
                    this.formData.waist_display = this.formData.waist_cm || '';
                    this.formData.inseam_display = this.formData.inseam_cm || '';
                    this.formData.bust_display = this.formData.bust_cm || '';
                    this.formData.hips_display = this.formData.hips_cm || '';
                } else {
                    this.formData.chest_display = this.formData.chest_cm ? this.roundToSingleDecimal(Number(this.formData.chest_cm) / 2.54) : '';
                    this.formData.waist_display = this.formData.waist_cm ? this.roundToSingleDecimal(Number(this.formData.waist_cm) / 2.54) : '';
                    this.formData.inseam_display = this.formData.inseam_cm ? this.roundToSingleDecimal(Number(this.formData.inseam_cm) / 2.54) : '';
                    this.formData.bust_display = this.formData.bust_cm ? this.roundToSingleDecimal(Number(this.formData.bust_cm) / 2.54) : '';
                    this.formData.hips_display = this.formData.hips_cm ? this.roundToSingleDecimal(Number(this.formData.hips_cm) / 2.54) : '';
                }
            },
            updateCanonicalMeasurements() {
                if (this.usesMetricHeight()) {
                    this.formData.height_cm = this.toNullableInteger(this.formData.height_display);
                } else {
                    const feet = Number(this.formData.height_feet || 0);
                    const inches = Number(this.formData.height_inches || 0);
                    this.formData.height_cm = feet || inches ? Math.round(((feet * 12) + inches) * 2.54) : '';
                }

                if (this.usesStoneWeight()) {
                    const stones = Number(this.formData.weight_stone || 0);
                    const pounds = Number(this.formData.weight_pounds || 0);
                    const totalPounds = stones * 14 + pounds;
                    this.formData.weight_kg = totalPounds ? this.roundToSingleDecimal(totalPounds * 0.453592) : '';
                } else if (this.usesPoundsWeight()) {
                    this.formData.weight_kg = this.convertToMetric(this.formData.weight_display, 0.453592);
                } else {
                    this.formData.weight_kg = this.toNullableDecimal(this.formData.weight_display);
                }

                if (this.usesMetricBodyMeasurements()) {
                    this.formData.chest_cm = this.toNullableDecimal(this.formData.chest_display);
                    this.formData.waist_cm = this.toNullableDecimal(this.formData.waist_display);
                    this.formData.inseam_cm = this.toNullableDecimal(this.formData.inseam_display);
                    this.formData.bust_cm = this.toNullableDecimal(this.formData.bust_display);
                    this.formData.hips_cm = this.toNullableDecimal(this.formData.hips_display);
                    return;
                }
                this.formData.chest_cm = this.convertToMetric(this.formData.chest_display, 2.54);
                this.formData.waist_cm = this.convertToMetric(this.formData.waist_display, 2.54);
                this.formData.inseam_cm = this.convertToMetric(this.formData.inseam_display, 2.54);
                this.formData.bust_cm = this.convertToMetric(this.formData.bust_display, 2.54);
                this.formData.hips_cm = this.convertToMetric(this.formData.hips_display, 2.54);
            },
            convertToMetric(value, multiplier) {
                const numeric = Number(value);
                if (!value || Number.isNaN(numeric)) {
                    return '';
                }

                return this.roundToSingleDecimal(numeric * multiplier);
            },
            toNullableInteger(value) {
                const numeric = Number(value);
                return !value || Number.isNaN(numeric) ? '' : Math.round(numeric);
            },
            toNullableDecimal(value) {
                const numeric = Number(value);
                return !value || Number.isNaN(numeric) ? '' : this.roundToSingleDecimal(numeric);
            },
            roundToSingleDecimal(value) {
                return Math.round(value * 10) / 10;
            }
        };
    }
    
    function customSelect(config) {
        return {
            options: config.options || [],
            selectedValue: config.selectedValue || '',
            selectedLabel: '',
            showDropdown: false,
            highlightedIndex: -1,
            
            init() {
                this.syncSelectedLabel();
            },

            syncSelectedLabel() {
                const selected = this.options.find(opt => opt.value === this.selectedValue);
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
            }
        };
    }

    window.positionFloatingDropdown = function positionFloatingDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        const container = dropdown.parentElement;
        const trigger = container ? container.querySelector('input[type=\"text\"], .cursor-pointer') : null;

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
    
    function searchableDropdown() {
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
                    console.error('No countries data provided');
                    return;
                }
                
                this.countries = Object.keys(countriesList).map(code => ({
                    code: code,
                    name: countriesList[code]
                })).sort((a, b) => a.name.localeCompare(b.name));
                
                // Initialize with first 50 countries visible
                this.filteredCountries = this.countries.slice(0, 50);
                
                if (selectedCode) {
                    const selected = this.countries.find(c => c.code === selectedCode);
                    if (selected) {
                        this.selectedValue = selected.code;
                        this.selectedLabel = selected.name;
                        this.searchInput = selected.name;
                    }
                }
            },
            
            filterCountries() {
                if (!this.countries || this.countries.length === 0) {
                    return;
                }
                
                const search = this.searchInput.toLowerCase().trim();
                if (!search) {
                    // Show all countries when search is empty
                    this.filteredCountries = this.countries.slice(0, 50); // Limit to first 50 for performance
                } else {
                    this.filteredCountries = this.countries.filter(country => 
                        country.name.toLowerCase().includes(search) || 
                        country.code.toLowerCase().includes(search)
                    );
                }
                this.highlightedIndex = -1;
            },
            
            selectCountry(country) {
                this.selectedValue = country.code;
                this.selectedLabel = country.name;
                this.searchInput = country.name;
                this.showDropdown = false;
                // Update the parent locationAutocomplete component
                if (window.locationAutocompleteInstance) {
                    window.locationAutocompleteInstance.selectedCountry = country.code;
                    window.locationAutocompleteInstance.onCountryChange();
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
            }
        };
    }
    
    function locationAutocomplete() {
        return {
            selectedCountry: '',
            cityInput: '',
            selectedGeonameId: null,
            selectedCountryName: '',
            suggestions: [],
            showSuggestions: false,
            highlightedIndex: -1,
            searchTimeout: null,
            
            init(countryCode, cityName, geonameId) {
                this.selectedCountry = countryCode || '';
                this.cityInput = cityName || '';
                this.selectedGeonameId = geonameId || null;
                
                // Store instance for country dropdown to access
                window.locationAutocompleteInstance = this;
                
                if (countryCode) {
                    const countries = @json(config('countries'));
                    this.selectedCountryName = countries[countryCode] || '';
                }
            },
            
            onCountryChange() {
                this.cityInput = '';
                this.selectedGeonameId = null;
                this.selectedCountryName = '';
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
                
                this.searchTimeout = setTimeout(() => {
                    fetch(`/api/locations?q=${encodeURIComponent(this.cityInput)}&country=${this.selectedCountry}&limit=10`)
                        .then(response => response.json())
                        .then(data => {
                            this.suggestions = data.data || [];
                            this.showSuggestions = this.suggestions.length > 0;
                            this.highlightedIndex = -1;
                        })
                        .catch(error => {
                            console.error('Error fetching cities:', error);
                            this.suggestions = [];
                        });
                }, 300);
            },
            
            selectCity(suggestion) {
                this.cityInput = suggestion.city;
                this.selectedGeonameId = suggestion.id;
                this.selectedCountryName = suggestion.country_name;
                this.suggestions = [];
                this.showSuggestions = false;
            }
        };
    }
</script>
