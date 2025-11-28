<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Your Photographer Profile') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="photographerProfileWizard()" x-init="init()">
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
            <form method="POST" action="{{ route('photographers.profile.update') }}" @submit.prevent="saveProfile()" id="profileForm" enctype="multipart/form-data">
                @csrf
                @method('patch')
                <input type="hidden" name="wizard_completion" value="1">

                @php
                    $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties();
                    $servicesOptions = \App\Helpers\PhotographerOptions::services();
                    $countriesData = config('countries');
                @endphp

                <!-- Step 1: Basic Information -->
                <div x-show="currentStep === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Tell Us About Yourself</h3>
                        <p class="text-gray-600">Let's start with the basics</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="bio" :value="__('Bio')" />
                            <textarea id="bio" name="bio" rows="4" x-model="formData.bio" @input="$dispatch('step1-validation-changed')" maxlength="1200" required class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 text-gray-900 placeholder-gray-400 resize-y" placeholder="Tell us about your photography style, experience, and what makes your work unique..."></textarea>
                            <div class="mt-1 flex justify-end">
                                <p class="text-xs" :class="(formData.bio || '').length >= 1200 ? 'text-red-600 font-semibold' : ((formData.bio || '').length < 50 ? 'text-red-600' : 'text-gray-900')">
                                    <span x-text="(formData.bio || '').length"></span> / 1200 characters
                                </p>
                            </div>
                            <x-input-error :messages="$errors?->get('bio') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="professional_name" :value="__('Professional/Business Name')" />
                            <x-text-input id="professional_name" name="professional_name" type="text" x-model="formData.professional_name" class="block mt-1 w-full" placeholder="e.g., ABC Photography, John Smith Photography" />
                            <p class="mt-1 text-xs text-gray-500">Optional - Your business or professional name if different from your real name</p>
                            <x-input-error :messages="$errors?->get('professional_name') ?? []" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="locationAutocomplete()" x-init="init(formData.locationCountryCode || '', formData.locationCity || '', formData.locationGeonameId || null); $watch('formData.locationCountryCode', value => { if (value) { selectedCountry = value; onCountryChange(); } }); $watch('selectedGeonameId', value => { formData.locationGeonameId = value; }); $watch('cityInput', value => { formData.locationCity = value; });" @location-updated.window="if ($event.detail && $event.detail.country) { selectedCountry = $event.detail.country; formData.locationCountryCode = $event.detail.country; onCountryChange(); }">
                            <div>
                                <x-input-label for="location_country_code" :value="__('Country')" />
                                <div class="relative mt-1" 
                                     x-data="searchableDropdown()" 
                                     x-init="initCountries(@js($countriesData), formData.locationCountryCode || '')"
                                     @country-selected.window="if ($event.detail && $event.detail.countryCode) { $dispatch('step1-validation-changed') }">
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
                                    <input type="hidden" name="location_country_code" x-model="selectedValue" @change="formData.locationCountryCode = selectedValue; $dispatch('step1-validation-changed')" required />
                                    <div x-show="showDropdown && filteredCountries.length > 0" 
                                         x-cloak
                                         x-transition
                                         x-init="
                                            $watch('showDropdown', value => {
                                                if (value) {
                                                    setTimeout(() => {
                                                        const dropdown = $el;
                                                        const input = document.getElementById('location_country_code');
                                                        if (!input) return;
                                                        const rect = input.getBoundingClientRect();
                                                        const viewportHeight = window.innerHeight;
                                                        const spaceBelow = viewportHeight - rect.bottom;
                                                        const spaceAbove = rect.top;
                                                        
                                                        if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                            dropdown.classList.add('bottom-full');
                                                            dropdown.classList.remove('mt-1');
                                                            dropdown.classList.add('mb-1');
                                                            dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                        } else {
                                                            dropdown.classList.remove('bottom-full', 'mb-1');
                                                            dropdown.classList.add('mt-1');
                                                            dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                        }
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
                                        @change="$dispatch('step1-validation-changed')"
                                        class="block mt-1 w-full" 
                                        placeholder="Start typing city name..." 
                                        autocomplete="off"
                                        required />
                                    <input type="hidden" name="location_geoname_id" x-model="selectedGeonameId" @change="$dispatch('step1-validation-changed')" required />
                                    <input type="hidden" name="location_country" x-model="selectedCountryName" />
                                    
                                    <div x-show="showSuggestions && suggestions.length > 0" 
                                         x-cloak
                                         x-init="
                                            $watch('showSuggestions', value => {
                                                if (value) {
                                                    setTimeout(() => {
                                                        const dropdown = $el;
                                                        const input = document.getElementById('location_city');
                                                        if (!input) return;
                                                        const rect = input.getBoundingClientRect();
                                                        const viewportHeight = window.innerHeight;
                                                        const spaceBelow = viewportHeight - rect.bottom;
                                                        const spaceAbove = rect.top;
                                                        
                                                        if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                            dropdown.classList.add('bottom-full');
                                                            dropdown.classList.remove('mt-1');
                                                            dropdown.classList.add('mb-1');
                                                            dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                        } else {
                                                            dropdown.classList.remove('bottom-full', 'mb-1');
                                                            dropdown.classList.add('mt-1');
                                                            dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                        }
                                                    }, 10);
                                                }
                                            });
                                         "
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                         style="max-height: 240px;">
                                        <template x-for="(suggestion, index) in suggestions" :key="index">
                                            <div @click="selectCity(suggestion); $dispatch('location-updated', {city: suggestion.city, geonameId: suggestion.id}); $dispatch('step1-validation-changed')" 
                                                 class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                <div class="font-medium text-black" x-text="suggestion.city"></div>
                                                <div class="text-sm text-gray-600" x-text="suggestion.label"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors?->get('location_city') ?? []" class="mt-2" />
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
                                    onSelect: (value) => { formData.gender = value; $dispatch('step1-validation-changed'); }
                                })">
                                    <input type="hidden" name="gender" x-model="selectedValue" @change="formData.gender = selectedValue; $dispatch('step1-validation-changed')" required />
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
                                                        const dropdown = $el;
                                                        const clickableDiv = dropdown.previousElementSibling?.previousElementSibling;
                                                        if (!clickableDiv) return;
                                                        const rect = clickableDiv.getBoundingClientRect();
                                                        const viewportHeight = window.innerHeight;
                                                        const spaceBelow = viewportHeight - rect.bottom;
                                                        const spaceAbove = rect.top;
                                                        
                                                        if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                            dropdown.classList.add('bottom-full');
                                                            dropdown.classList.remove('mt-1');
                                                            dropdown.classList.add('mb-1');
                                                            dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                        } else {
                                                            dropdown.classList.remove('bottom-full', 'mb-1');
                                                            dropdown.classList.add('mt-1');
                                                            dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                        }
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
                                <x-input-error :messages="$errors?->get('gender') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="experience_start_year" :value="__('What year did you start photography?')" />
                                <x-text-input id="experience_start_year" name="experience_start_year" type="number" x-model="formData.experience_start_year" class="block mt-1 w-full" min="1900" max="{{ date('Y') }}" placeholder="e.g., 2015" />
                                <p class="mt-1 text-xs text-gray-500">Optional - helps show your experience level</p>
                                <x-input-error :messages="$errors?->get('experience_start_year') ?? []" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Professional Details -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Professional Details</h3>
                        <p class="text-gray-600">Tell us about your photography expertise</p>
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
                                                    const dropdown = $el;
                                                    const input = dropdown.previousElementSibling.previousElementSibling;
                                                    const rect = input.getBoundingClientRect();
                                                    const viewportHeight = window.innerHeight;
                                                    const spaceBelow = viewportHeight - rect.bottom;
                                                    const spaceAbove = rect.top;
                                                    
                                                    if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                        dropdown.classList.add('bottom-full');
                                                        dropdown.classList.remove('mt-1');
                                                        dropdown.classList.add('mb-1');
                                                        dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                    } else {
                                                        dropdown.classList.remove('bottom-full', 'mb-1');
                                                        dropdown.classList.add('mt-1');
                                                        dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                    }
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
                            <x-input-error :messages="$errors?->get('experience_level') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Specialties')" />
                            <p class="text-sm text-gray-600 mb-4">Select the types of photography you specialize in</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto pr-2">
                                @foreach($specialtiesOptions as $key => $label)
                                    <label class="flex items-start cursor-pointer p-4 border-2 rounded-lg hover:border-black hover:shadow-md transition-all duration-200 group"
                                           :class="formData.specialties.includes('{{ $key }}') ? 'border-black bg-black text-white shadow-lg' : 'border-gray-300 bg-white'">
                                        <input type="checkbox" 
                                               name="specialties[]" 
                                               value="{{ $key }}"
                                               x-model="formData.specialties"
                                               class="mt-0.5 w-5 h-5 rounded border-2 border-gray-400 text-black focus:ring-2 focus:ring-black focus:ring-offset-2 cursor-pointer transition-all"
                                               :class="formData.specialties.includes('{{ $key }}') ? 'border-white bg-white' : ''">
                                        <span class="ml-3 text-sm font-medium flex-1" 
                                              :class="formData.specialties.includes('{{ $key }}') ? 'text-white' : 'text-gray-700 group-hover:text-black'">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors?->get('specialties') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Services Offered')" />
                            <p class="text-sm text-gray-600 mb-4">Select the services you offer</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto pr-2">
                                @foreach($servicesOptions as $key => $label)
                                    <label class="flex items-start cursor-pointer p-4 border-2 rounded-lg hover:border-black hover:shadow-md transition-all duration-200 group"
                                           :class="formData.services.includes('{{ $key }}') ? 'border-black bg-black text-white shadow-lg' : 'border-gray-300 bg-white'">
                                        <input type="checkbox" 
                                               name="services_offered[]" 
                                               value="{{ $key }}"
                                               x-model="formData.services"
                                               class="mt-0.5 w-5 h-5 rounded border-2 border-gray-400 text-black focus:ring-2 focus:ring-black focus:ring-offset-2 cursor-pointer transition-all"
                                               :class="formData.services.includes('{{ $key }}') ? 'border-white bg-white' : ''">
                                        <span class="ml-3 text-sm font-medium flex-1"
                                              :class="formData.services.includes('{{ $key }}') ? 'text-white' : 'text-gray-700 group-hover:text-black'">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors?->get('services_offered') ?? []" class="mt-2" />
                        </div>

                        <!-- Studio Location (conditional) -->
                        <div x-show="formData.services.includes('studio-photo-sessions') || formData.services.includes('studio-rental')" 
                             x-transition:enter="transition ease-out duration-300" 
                             x-transition:enter-start="opacity-0 transform translate-y-2" 
                             x-transition:enter-end="opacity-100 transform translate-y-0">
                            <x-input-label for="studio_location" :value="__('Studio Location')" />
                            <div x-data="{ 
                                showStudioLocationEditor: false,
                                originalCity: '',
                                originalCountry: '',
                                originalCountryCode: '',
                                originalGeonameId: null,
                                updateStudioLocation(data) {
                                    if (data.city) formData.studioLocationCity = data.city;
                                    if (data.country) formData.studioLocationCountry = data.country;
                                },
                                openEditor() {
                                    // Store original values
                                    this.originalCity = formData.studioLocationCity || '';
                                    this.originalCountry = formData.studioLocationCountry || '';
                                    this.originalCountryCode = formData.studioLocationCountryCode || '';
                                    this.originalGeonameId = formData.studioLocationGeonameId || null;
                                    this.showStudioLocationEditor = true;
                                },
                                cancelEditor() {
                                    // Restore original values
                                    formData.studioLocationCity = this.originalCity;
                                    formData.studioLocationCountry = this.originalCountry;
                                    formData.studioLocationCountryCode = this.originalCountryCode;
                                    formData.studioLocationGeonameId = this.originalGeonameId;
                                    this.showStudioLocationEditor = false;
                                }
                            }" @studio-location-updated.window="updateStudioLocation($event.detail)" @cancel-studio-location.window="cancelEditor()" @close-studio-editor.window="showStudioLocationEditor = false">
                                <!-- Display current location -->
                                <div x-show="!showStudioLocationEditor" class="mt-1">
                                    <div class="flex items-center justify-between p-3 border-2 border-gray-800 rounded-md bg-white">
                                        <div>
                                            <span x-show="formData.studioLocationCity || formData.studioLocationCountry" class="text-gray-900 font-medium">
                                                <span x-text="formData.studioLocationCity || ''"></span><span x-show="formData.studioLocationCity && formData.studioLocationCountry">, </span><span x-text="formData.studioLocationCountry || ''"></span>
                                            </span>
                                            <span x-show="!formData.studioLocationCity && !formData.studioLocationCountry" class="text-gray-400 italic">
                                                <span x-show="formData.locationCity || formData.locationCountry">
                                                    <span x-text="formData.locationCity || ''"></span><span x-show="formData.locationCity && formData.locationCountry">, </span><span x-text="formData.locationCountry || ''"></span>
                                                    <span class="text-xs ml-2">(using main location)</span>
                                                </span>
                                                <span x-show="!formData.locationCity && !formData.locationCountry">No location set</span>
                                            </span>
                                        </div>
                                        <button type="button" @click="openEditor()" class="text-sm text-gray-600 hover:text-black underline">
                                            Change Studio Location
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Studio location editor -->
                                <div x-show="showStudioLocationEditor" 
                                     x-transition
                                     class="mt-1"
                                     x-data="locationAutocomplete()" 
                                     x-init="init(formData.studioLocationCountryCode || formData.locationCountryCode || '', formData.studioLocationCity || formData.locationCity || '', formData.studioLocationGeonameId || formData.locationGeonameId || null); $watch('formData.studioLocationCountryCode', value => { if (value) { selectedCountry = value; onCountryChange(); } })"
                                     @studio-location-updated.window="if ($event.detail && $event.detail.countryCode) { selectedCountry = $event.detail.countryCode; onCountryChange(); }">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="studio_location_country_code" :value="__('Country')" />
                                            <div class="relative mt-1" 
                                                 x-data="searchableDropdown()" 
                                                 x-init="initCountries(@js($countriesData), formData.studioLocationCountryCode || formData.locationCountryCode || '')">
                                                <div class="relative">
                                                    <x-text-input 
                                                        id="studio_location_country_code" 
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
                                                <input type="hidden" name="studio_location_country_code" x-model="selectedValue" @change="formData.studioLocationCountryCode = selectedValue; const selectedCountryObj = countries.find(c => c.code === selectedValue); $dispatch('studio-location-updated', {country: selectedCountryObj ? selectedCountryObj.name : selectedValue, countryCode: selectedValue})" />
                                                <div x-show="showDropdown && filteredCountries.length > 0" 
                                                     x-cloak
                                                     x-transition
                                                     x-init="
                                                        $watch('showDropdown', value => {
                                                            if (value) {
                                                                setTimeout(() => {
                                                                    const dropdown = $el;
                                                                    const input = document.getElementById('studio_location_country_code');
                                                                    if (!input) return;
                                                                    const rect = input.getBoundingClientRect();
                                                                    const viewportHeight = window.innerHeight;
                                                                    const spaceBelow = viewportHeight - rect.bottom;
                                                                    const spaceAbove = rect.top;
                                                                    
                                                                    if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                                        dropdown.classList.add('bottom-full');
                                                                        dropdown.classList.remove('mt-1');
                                                                        dropdown.classList.add('mb-1');
                                                                        dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                                    } else {
                                                                        dropdown.classList.remove('bottom-full', 'mb-1');
                                                                        dropdown.classList.add('mt-1');
                                                                        dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                                    }
                                                                }, 10);
                                                            }
                                                        });
                                                     "
                                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                     style="max-height: 240px;">
                                                    <template x-for="(country, index) in filteredCountries" :key="country.code">
                                                        <div @click="selectCountry(country); formData.studioLocationCountryCode = country.code; $dispatch('studio-location-updated', {country: country.name, countryCode: country.code})" 
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
                                        </div>

                                        <div>
                                            <x-input-label for="studio_location_city" :value="__('City')" />
                                            <div class="relative mt-1">
                                                <x-text-input 
                                                    id="studio_location_city" 
                                                    name="studio_location_city" 
                                                    type="text" 
                                                    x-model="cityInput"
                                                    @input="searchCities()"
                                                    @focus="showSuggestions = true"
                                                    @blur="setTimeout(() => showSuggestions = false, 200)"
                                                    class="block w-full" 
                                                    placeholder="Start typing city name..." 
                                                    autocomplete="off" />
                                                <input type="hidden" name="studio_location_geoname_id" x-model="selectedGeonameId" @change="formData.studioLocationGeonameId = selectedGeonameId" />
                                                <input type="hidden" name="studio_location_country" x-model="selectedCountryName" @change="formData.studioLocationCountry = selectedCountryName" />
                                                
                                                <div x-show="showSuggestions && suggestions.length > 0" 
                                                     x-cloak
                                                     x-init="
                                                        $watch('showSuggestions', value => {
                                                            if (value) {
                                                                setTimeout(() => {
                                                                    const dropdown = $el;
                                                                    const input = document.getElementById('studio_location_city');
                                                                    if (!input) return;
                                                                    const rect = input.getBoundingClientRect();
                                                                    const viewportHeight = window.innerHeight;
                                                                    const spaceBelow = viewportHeight - rect.bottom;
                                                                    const spaceAbove = rect.top;
                                                                    
                                                                    if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                                        dropdown.classList.add('bottom-full');
                                                                        dropdown.classList.remove('mt-1');
                                                                        dropdown.classList.add('mb-1');
                                                                        dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                                    } else {
                                                                        dropdown.classList.remove('bottom-full', 'mb-1');
                                                                        dropdown.classList.add('mt-1');
                                                                        dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                                    }
                                                                }, 10);
                                                            }
                                                        });
                                                     "
                                                     class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                     style="max-height: 240px;">
                                                    <template x-for="(suggestion, index) in suggestions" :key="index">
                                                        <div @click="selectCity(suggestion); formData.studioLocationGeonameId = suggestion.id; const countryParts = suggestion.label.split(', '); $dispatch('studio-location-updated', {city: suggestion.city, country: countryParts.length > 1 ? countryParts[1] : ''})" 
                                                             class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                            <div class="font-medium text-black" x-text="suggestion.city"></div>
                                                            <div class="text-sm text-gray-600" x-text="suggestion.label"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center gap-4" x-data @click.outside.stop>
                                        <button type="button" @click="window.dispatchEvent(new CustomEvent('cancel-studio-location'))" class="text-sm text-gray-600 hover:text-black underline">
                                            Cancel
                                        </button>
                                        <button type="button" @click="$dispatch('close-studio-editor')" class="text-sm bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                                            Save Location
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Optional - Where is your studio located? If not set, your main location will be used.</p>
                            <input type="hidden" name="studio_location" :value="(formData.studioLocationCity && formData.studioLocationCountry) ? formData.studioLocationCity + ', ' + formData.studioLocationCountry : (formData.locationCity && formData.locationCountry) ? formData.locationCity + ', ' + formData.locationCountry : ''" />
                            <x-input-error :messages="$errors?->get('studio_location') ?? []" class="mt-2" />
                        </div>

                        <!-- Available for Travel -->
                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="available_for_travel" value="1" x-model="formData.available_for_travel" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Available for travel</span>
                            </label>
                            <p class="mt-2 ml-8 text-xs text-gray-500">Check this if you're willing to travel for photo shoots</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Equipment -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Your Equipment</h3>
                        <p class="text-gray-600">Show off your gear (optional but helps build credibility)</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Cameras -->
                        <div>
                            <x-input-label :value="__('Cameras')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.cameras" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.cameras.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newCamera" @keyup.enter.prevent="if(newCamera.trim()) { formData.equipment.cameras.push(newCamera.trim()); newCamera = ''; }" placeholder="e.g., Canon EOS R5" class="flex-1" />
                                <button type="button" @click="if(newCamera.trim()) { formData.equipment.cameras.push(newCamera.trim()); newCamera = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Lenses -->
                        <div>
                            <x-input-label :value="__('Lenses')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.lenses" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.lenses.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newLens" @keyup.enter.prevent="if(newLens.trim()) { formData.equipment.lenses.push(newLens.trim()); newLens = ''; }" placeholder="e.g., 24-70mm f/2.8" class="flex-1" />
                                <button type="button" @click="if(newLens.trim()) { formData.equipment.lenses.push(newLens.trim()); newLens = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Lighting -->
                        <div>
                            <x-input-label :value="__('Lighting Equipment')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.lighting" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.lighting.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newLighting" @keyup.enter.prevent="if(newLighting.trim()) { formData.equipment.lighting.push(newLighting.trim()); newLighting = ''; }" placeholder="e.g., Profoto B10" class="flex-1" />
                                <button type="button" @click="if(newLighting.trim()) { formData.equipment.lighting.push(newLighting.trim()); newLighting = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Other Equipment -->
                        <div>
                            <x-input-label :value="__('Other Equipment')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.other" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.other.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newOther" @keyup.enter.prevent="if(newOther.trim()) { formData.equipment.other.push(newOther.trim()); newOther = ''; }" placeholder="e.g., Tripod, Backdrops, etc." class="flex-1" />
                                <button type="button" @click="if(newOther.trim()) { formData.equipment.other.push(newOther.trim()); newOther = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Contact & Social -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Contact & Social Links</h3>
                        <p class="text-gray-600">How can people reach you?</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="public_email" :value="__('Public Email')" />
                            <x-text-input id="public_email" name="public_email" type="email" x-model="formData.public_email" class="block mt-1 w-full" placeholder="your@email.com" />
                            <p class="mt-1 text-xs text-gray-500">This will be visible on your public profile</p>
                            <x-input-error :messages="$errors?->get('public_email') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" x-model="formData.phone" class="block mt-1 w-full" placeholder="+1 234 567 8900" />
                            <x-input-error :messages="$errors?->get('phone') ?? []" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="instagram" :value="__('Instagram')" />
                                <x-text-input id="instagram" name="instagram" type="text" x-model="formData.instagram" class="block mt-1 w-full" placeholder="@username" />
                                <x-input-error :messages="$errors?->get('instagram') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="facebook" :value="__('Facebook')" />
                                <x-text-input id="facebook" name="facebook" type="text" x-model="formData.facebook" class="block mt-1 w-full" placeholder="Your Facebook page" />
                                <x-input-error :messages="$errors?->get('facebook') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="twitter" :value="__('Twitter/X')" />
                                <x-text-input id="twitter" name="twitter" type="text" x-model="formData.twitter" class="block mt-1 w-full" placeholder="@username" />
                                <x-input-error :messages="$errors?->get('twitter') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="portfolio_website" :value="__('Portfolio Website')" />
                                <x-text-input id="portfolio_website" name="portfolio_website" type="url" x-model="formData.portfolio_website" class="block mt-1 w-full" placeholder="https://yourportfolio.com" />
                                <x-input-error :messages="$errors?->get('portfolio_website') ?? []" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Photos & Settings -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Almost Done!</h3>
                        <p class="text-gray-600">Add your photos and final settings</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Profile Photo Upload with Crop -->
                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200" x-data="imageCropper('profile_photo', formData.professional_name ? null : true)">
                            <x-input-label for="profile_photo" :value="__('Profile Photo')" />
                            <p class="mt-1 text-xs text-gray-500 mb-4">Upload a photo of yourself.</p>
                            
                            <button type="button" @click="$refs.profilePhotoInput.click()" class="mt-2 bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition">
                                <i class="fas fa-upload mr-2"></i>Choose Photo
                            </button>
                            <input type="file" x-ref="profilePhotoInput" id="profile_photo" name="profile_photo" @change="handleFileSelect($event)" accept="image/jpeg,image/jpg,image/png,image/heic,image/heif" style="display: none;">
                            <input type="hidden" name="profile_photo_crop_data" x-model="cropData" />
                            
                            <!-- Crop Modal -->
                            <div x-show="showCropModal" x-cloak class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" @click.self="showCropModal = false">
                                <div class="bg-white rounded-lg p-6 max-w-4xl w-full max-h-[90vh] overflow-auto">
                                    <h4 class="text-xl font-bold mb-4">Crop Your Photo</h4>
                                    <p class="text-sm text-gray-600 mb-4">Drag the crop area to position your photo.</p>
                                    
                                    <div class="relative" style="max-height: 600px; overflow: auto;">
                                        <canvas x-ref="cropCanvas" class="max-w-full border-2 border-gray-800"></canvas>
                                    </div>
                                    
                                    <div class="mt-4 flex justify-end gap-4">
                                        <button type="button" @click="cancelCrop()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                            Cancel
                                        </button>
                                        <button type="button" @click="applyCrop()" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
                                            Apply Crop
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Preview -->
                            <div x-show="previewUrl" class="mt-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                                <img :src="previewUrl" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-800">
                            </div>
                            
                            @if(isset($profile) && $profile->profile_photo_path)
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current photo:</p>
                                    <img src="{{ asset($profile->profile_photo_path) }}" alt="Current profile photo" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300">
                                </div>
                            @endif
                            
                            <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                        </div>

                        <!-- Logo Upload (conditional) -->
                        <div x-show="formData.professional_name" 
                             x-transition
                             class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200"
                             x-data="logoUploader()">
                            <x-input-label for="logo" :value="__('Company Logo')" />
                            <p class="mt-1 text-xs text-gray-500 mb-2">Since you entered "<span x-text="formData.professional_name"></span>", you can upload your company logo.</p>
                            
                            <input 
                                type="file" 
                                id="logo" 
                                name="logo" 
                                accept="image/jpeg,image/jpg,image/png,image/svg+xml"
                                @change="handleFileSelect($event)"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-2 file:border-gray-800 file:text-sm file:font-semibold file:bg-white file:text-gray-800 hover:file:bg-gray-50"
                            />
                            <p class="mt-2 text-sm text-gray-600">Maximum 800px on longest edge. Supports JPG, PNG, and SVG.</p>
                            
                            <!-- Preview -->
                            <div x-show="previewUrl" class="mt-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                                <img :src="previewUrl" alt="Logo preview" class="max-w-xs max-h-32 object-contain rounded-lg border-2 border-gray-800">
                            </div>
                            
                            @if(isset($profile) && $profile->logo_path)
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current logo:</p>
                                    <img src="{{ asset($profile->logo_path) }}" alt="Current logo" class="max-w-xs max-h-32 object-contain rounded-lg border-2 border-gray-300">
                                </div>
                            @endif
                            
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_public" value="1" x-model="formData.is_public" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Make profile public</span>
                            </label>
                            <p class="mt-2 ml-8 text-xs text-gray-500">When enabled, your profile will be visible to models and other users</p>
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
                <input type="hidden" name="professional_name" x-model="formData.professional_name" />
                <input type="hidden" name="experience_start_year" x-model="formData.experience_start_year" />
                <input type="hidden" name="experience_level" x-model="formData.experience_level" />
                <template x-for="specialty in formData.specialties" :key="specialty">
                    <input type="hidden" name="specialties[]" :value="specialty" />
                </template>
                <template x-for="service in formData.services" :key="service">
                    <input type="hidden" name="services_offered[]" :value="service" />
                </template>
                <input type="hidden" name="equipment" :value="JSON.stringify(formData.equipment)" />
                <input type="hidden" name="studio_location" x-model="formData.studio_location" />
                <input type="hidden" name="available_for_travel" :value="formData.available_for_travel ? 1 : 0" />
                <input type="hidden" name="public_email" x-model="formData.public_email" />
                <input type="hidden" name="phone" x-model="formData.phone" />
                <input type="hidden" name="instagram" x-model="formData.instagram" />
                <input type="hidden" name="facebook" x-model="formData.facebook" />
                <input type="hidden" name="twitter" x-model="formData.twitter" />
                <input type="hidden" name="portfolio_website" x-model="formData.portfolio_website" />
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
                        :disabled="!canProceedToNextStep()"
                        :class="canProceedToNextStep() ? 'px-6 py-3 bg-black text-white rounded-lg font-semibold hover:bg-gray-800 transition cursor-pointer' : 'px-6 py-3 bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed'">
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
    function photographerProfileWizard() {
        return {
            currentStep: 0,
            steps: [
                { title: 'Basic Info', icon: 'user' },
                { title: 'Professional', icon: 'briefcase' },
                { title: 'Equipment', icon: 'camera' },
                { title: 'Contact', icon: 'envelope' },
                { title: 'Settings', icon: 'cog' }
            ],
            formData: {
                bio: '',
                professional_name: '',
                locationCountryCode: '',
                locationCity: '',
                locationGeonameId: null,
                gender: '',
                experience_start_year: '',
                experience_level: '',
                specialties: [],
                services: [],
                equipment: {
                    cameras: [],
                    lenses: [],
                    lighting: [],
                    other: []
                },
                studio_location: '',
                studioLocationCity: '',
                studioLocationCountry: '',
                studioLocationCountryCode: '',
                studioLocationGeonameId: null,
                available_for_travel: false,
                public_email: '',
                phone: '',
                instagram: '',
                facebook: '',
                twitter: '',
                portfolio_website: '',
                is_public: true,
                contains_nudity: false
            },
            newCamera: '',
            newLens: '',
            newLighting: '',
            newOther: '',
            canProceedToNextStep() {
                // Step 1 validation: bio (50-1200 chars), country, city (geoname_id), and gender are required
                if (this.currentStep === 0) {
                    const bio = (this.formData.bio || '').trim();
                    return bio.length >= 50 && 
                           bio.length <= 1200 &&
                           this.formData.locationCountryCode && 
                           this.formData.locationCity && 
                           this.formData.locationCity.trim().length > 0 &&
                           this.formData.locationGeonameId &&
                           this.formData.gender;
                }
                // Other steps can proceed
                return true;
            },
            init() {
                // Store instance for external access
                window.photographerWizardInstance = this;
                
                // Listen for validation change events
                window.addEventListener('step1-validation-changed', () => {
                    // Trigger reactivity check
                    this.$nextTick(() => {});
                });
                
                // Load existing profile data if available
                @php
                    $hasProfile = isset($profile) && method_exists($profile, 'exists') && $profile->exists;
                    $wizardData = null;
                    if ($hasProfile && $profile->id) {
                        $equipment = $profile->equipment ?? [];
                        if (!is_array($equipment) || !isset($equipment['cameras'])) {
                            $equipment = ['cameras' => [], 'lenses' => [], 'lighting' => [], 'other' => []];
                        }
                        $locationCountry = '';
                        if ($profile->location_country_code) {
                            $countries = config('countries', []);
                            $locationCountry = $countries[$profile->location_country_code] ?? $profile->location_country ?? '';
                        } else {
                            $locationCountry = $profile->location_country ?? '';
                        }
                        
                        $wizardData = [
                            'bio' => $profile->bio ?? '',
                            'professional_name' => $profile->professional_name ?? '',
                            'locationCountryCode' => $profile->location_country_code ?? '',
                            'locationCity' => $profile->location_city ?? '',
                            'locationCountry' => $locationCountry,
                            'locationGeonameId' => $profile->location_geoname_id ?? null,
                            'gender' => $profile->gender ?? '',
                            'experience_start_year' => $profile->experience_start_year ?? '',
                            'experience_level' => $profile->experience_level ?? '',
                            'specialties' => $profile->specialties ?? [],
                            'services' => $profile->services_offered ?? [],
                            'equipment' => $equipment,
                            'studio_location' => $profile->studio_location ?? '',
                            'studioLocationCity' => ($profile->studio_location && strpos($profile->studio_location, ', ') !== false) ? explode(', ', $profile->studio_location)[0] : '',
                            'studioLocationCountry' => ($profile->studio_location && strpos($profile->studio_location, ', ') !== false) ? explode(', ', $profile->studio_location)[1] : '',
                            'studioLocationCountryCode' => '',
                            'studioLocationGeonameId' => null,
                            'available_for_travel' => (bool)($profile->available_for_travel ?? false),
                            'public_email' => $profile->public_email ?? '',
                            'phone' => $profile->phone ?? '',
                            'instagram' => $profile->instagram ?? '',
                            'facebook' => $profile->facebook ?? '',
                            'twitter' => $profile->twitter ?? '',
                            'portfolio_website' => $profile->portfolio_website ?? '',
                            'is_public' => (bool)($profile->is_public ?? true),
                            'contains_nudity' => (bool)($profile->contains_nudity ?? false)
                        ];
                    }
                @endphp
                @if($wizardData)
                    const wizardData = @json($wizardData);
                    Object.assign(this.formData, wizardData);
                @endif
            },
            nextStep() {
                if (!this.canProceedToNextStep()) {
                    return;
                }
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
                    if (key === 'equipment') {
                        formData.append('equipment', JSON.stringify(this.formData[key]));
                    } else if (Array.isArray(this.formData[key])) {
                        this.formData[key].forEach(item => {
                            formData.append(key + '[]', item);
                        });
                    } else {
                        formData.append(key, this.formData[key] || '');
                    }
                });
                formData.append('_method', 'PATCH');
                
                fetch('{{ route("photographers.profile.update") }}', {
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
                document.getElementById('profileForm').submit();
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
                // Find selected option and set label
                const selected = this.options.find(opt => opt.value === this.selectedValue);
                if (selected) {
                    this.selectedLabel = selected.label;
                }
            },
            
            selectOption(value) {
                this.selectedValue = value;
                const selected = this.options.find(opt => opt.value === value);
                this.selectedLabel = selected ? selected.label : '';
                this.showDropdown = false;
                if (config.onSelect) {
                    config.onSelect(value);
                }
            }
        };
    }
    
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
                    console.error('No countries data provided', countriesList);
                    this.filteredCountries = [];
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
                
                console.log('Countries loaded:', this.countries.length, 'Filtered:', this.filteredCountries.length);
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
                // Dispatch validation change event
                window.dispatchEvent(new CustomEvent('step1-validation-changed'));
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
                // Update selectedCountryName from countries config
                if (this.selectedCountry) {
                    const countries = @json(config('countries'));
                    this.selectedCountryName = countries[this.selectedCountry] || '';
                }
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
    
    function imageCropper(fieldName, isRequired = false) {
        return {
            showCropModal: false,
            previewUrl: null,
            cropData: null,
            originalImage: null,
            originalFile: null,
            canvas: null,
            ctx: null,
            image: null,
            cropX: 0,
            cropY: 0,
            cropSize: 400,
            scale: 1,
            imageX: 0,
            imageY: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                this.originalFile = file;
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    this.originalImage = new Image();
                    this.originalImage.onload = () => {
                        this.showCropModal = true;
                        this.$nextTick(() => {
                            this.initCropper();
                        });
                    };
                    this.originalImage.src = e.target.result;
                };
                
                reader.readAsDataURL(file);
            },
            
            initCropper() {
                this.canvas = this.$refs.cropCanvas;
                this.ctx = this.canvas.getContext('2d');
                
                // Set canvas size (max 800px width/height)
                const maxSize = 600;
                const scale = Math.min(maxSize / this.originalImage.width, maxSize / this.originalImage.height);
                this.canvas.width = this.originalImage.width * scale;
                this.canvas.height = this.originalImage.height * scale;
                this.scale = scale;
                
                // Initial crop size (80% of smaller dimension)
                this.cropSize = Math.min(this.canvas.width, this.canvas.height) * 0.8;
                
                // Center crop area
                this.cropX = (this.canvas.width - this.cropSize) / 2;
                this.cropY = (this.canvas.height - this.cropSize) / 2;
                
                this.draw();
                
                // Add mouse/touch event listeners
                // Use document-level listeners for mouse to allow dragging outside canvas
                this.canvas.addEventListener('mousedown', this.onMouseDown.bind(this));
                document.addEventListener('mousemove', this.onMouseMoveBound = this.onMouseMove.bind(this));
                document.addEventListener('mouseup', this.onMouseUpBound = this.onMouseUp.bind(this));
                this.canvas.addEventListener('touchstart', this.onTouchStart.bind(this), { passive: false });
                this.canvas.addEventListener('touchmove', this.onTouchMove.bind(this), { passive: false });
                this.canvas.addEventListener('touchend', this.onTouchEnd.bind(this), { passive: false });
            },
            
            draw() {
                if (!this.ctx || !this.originalImage) return;
                
                // Clear canvas
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                
                // Draw image
                this.ctx.drawImage(this.originalImage, 0, 0, this.canvas.width, this.canvas.height);
                
                // Draw overlay (darken non-crop area)
                this.ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
                this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
                
                // Clear crop area
                this.ctx.save();
                this.ctx.globalCompositeOperation = 'destination-out';
                this.ctx.fillRect(this.cropX, this.cropY, this.cropSize, this.cropSize);
                this.ctx.restore();
                
                // Draw crop border
                this.ctx.strokeStyle = '#fff';
                this.ctx.lineWidth = 2;
                this.ctx.strokeRect(this.cropX, this.cropY, this.cropSize, this.cropSize);
                
                // Draw corner handles
                const handleSize = 10;
                this.ctx.fillStyle = '#fff';
                const corners = [
                    [this.cropX, this.cropY],
                    [this.cropX + this.cropSize, this.cropY],
                    [this.cropX, this.cropY + this.cropSize],
                    [this.cropX + this.cropSize, this.cropY + this.cropSize]
                ];
                corners.forEach(([x, y]) => {
                    this.ctx.fillRect(x - handleSize/2, y - handleSize/2, handleSize, handleSize);
                });
            },
            
            getMousePos(e) {
                const rect = this.canvas.getBoundingClientRect();
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
            },
            
            getTouchPos(e) {
                const rect = this.canvas.getBoundingClientRect();
                const touch = e.touches[0] || e.changedTouches[0];
                return {
                    x: touch.clientX - rect.left,
                    y: touch.clientY - rect.top
                };
            },
            
            onMouseDown(e) {
                const pos = this.getMousePos(e);
                if (this.isInCropArea(pos.x, pos.y)) {
                    this.isDragging = true;
                    this.dragStartX = pos.x - this.cropX;
                    this.dragStartY = pos.y - this.cropY;
                }
            },
            
            onMouseMove(e) {
                if (!this.isDragging || !this.canvas) return;
                const rect = this.canvas.getBoundingClientRect();
                const pos = {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
                this.cropX = Math.max(0, Math.min(pos.x - this.dragStartX, this.canvas.width - this.cropSize));
                this.cropY = Math.max(0, Math.min(pos.y - this.dragStartY, this.canvas.height - this.cropSize));
                this.draw();
            },
            
            onMouseUp(e) {
                this.isDragging = false;
            },
            
            onTouchStart(e) {
                e.preventDefault();
                const pos = this.getTouchPos(e);
                if (this.isInCropArea(pos.x, pos.y)) {
                    this.isDragging = true;
                    this.dragStartX = pos.x - this.cropX;
                    this.dragStartY = pos.y - this.cropY;
                }
            },
            
            onTouchMove(e) {
                e.preventDefault();
                if (!this.isDragging) return;
                const pos = this.getTouchPos(e);
                this.cropX = Math.max(0, Math.min(pos.x - this.dragStartX, this.canvas.width - this.cropSize));
                this.cropY = Math.max(0, Math.min(pos.y - this.dragStartY, this.canvas.height - this.cropSize));
                this.draw();
            },
            
            onTouchEnd(e) {
                e.preventDefault();
                this.isDragging = false;
            },
            
            isInCropArea(x, y) {
                return x >= this.cropX && x <= this.cropX + this.cropSize &&
                       y >= this.cropY && y <= this.cropY + this.cropSize;
            },
            
            cancelCrop() {
                // Remove document-level event listeners
                if (this.onMouseMoveBound) {
                    document.removeEventListener('mousemove', this.onMouseMoveBound);
                }
                if (this.onMouseUpBound) {
                    document.removeEventListener('mouseup', this.onMouseUpBound);
                }
                
                this.showCropModal = false;
                this.originalImage = null;
                this.originalFile = null;
                this.isDragging = false;
            },
            
            applyCrop() {
                // Calculate crop coordinates in original image dimensions
                const sourceX = this.cropX / this.scale;
                const sourceY = this.cropY / this.scale;
                const sourceWidth = this.cropSize / this.scale;
                const sourceHeight = this.cropSize / this.scale;
                
                // Store crop data
                this.cropData = JSON.stringify({
                    x: sourceX,
                    y: sourceY,
                    width: sourceWidth,
                    height: sourceHeight,
                    imageWidth: this.originalImage.width,
                    imageHeight: this.originalImage.height
                });
                
                // Create preview (800x800)
                const previewCanvas = document.createElement('canvas');
                previewCanvas.width = 800;
                previewCanvas.height = 800;
                const previewCtx = previewCanvas.getContext('2d');
                
                // Draw cropped and resized image
                previewCtx.drawImage(
                    this.originalImage,
                    sourceX, sourceY, sourceWidth, sourceHeight,
                    0, 0, 800, 800
                );
                
                this.previewUrl = previewCanvas.toDataURL('image/jpeg', 0.9);
                
                // Remove document-level event listeners
                if (this.onMouseMoveBound) {
                    document.removeEventListener('mousemove', this.onMouseMoveBound);
                }
                if (this.onMouseUpBound) {
                    document.removeEventListener('mouseup', this.onMouseUpBound);
                }
                
                this.showCropModal = false;
                this.isDragging = false;
                
                // Update file input (create a new File from the cropped canvas)
                previewCanvas.toBlob((blob) => {
                    const croppedFile = new File([blob], this.originalFile.name, { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    const fileInput = document.getElementById('profile_photo');
                    if (fileInput) {
                        fileInput.files = dataTransfer.files;
                    }
                }, 'image/jpeg', 0.9);
            }
        };
    }
    
    function logoUploader() {
        return {
            previewUrl: null,
            
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
    }
</script>

