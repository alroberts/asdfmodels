<x-app-layout>
    <x-slot name="header">
        <div class="browse-hero">
            <p class="browse-kicker">Photographers</p>
            <div>
                <h1>Find a Photographer</h1>
                <p>Discover verified creatives by location, services, style, and availability.</p>
            </div>
        </div>
    </x-slot>

    <style>
        .browse-shell { max-width: 1180px; margin: 0 auto; padding: 40px 24px 72px; }
        .browse-hero { display: flex; flex-direction: column; gap: 10px; }
        .browse-hero h1 { margin: 0; font-size: clamp(32px, 4vw, 56px); line-height: .95; letter-spacing: -.05em; color: #111827; }
        .browse-hero p { margin: 0; max-width: 680px; color: #4b5563; font-size: 16px; line-height: 1.6; }
        .browse-kicker { font-size: 12px !important; letter-spacing: .35em; text-transform: uppercase; color: #6b7280 !important; font-weight: 800; }
        .browse-filters { margin-bottom: 28px; padding: 18px; border: 1px solid #e5e7eb; border-radius: 28px; background: linear-gradient(135deg, #fff 0%, #f8fafc 100%); box-shadow: 0 24px 60px rgba(15, 23, 42, .07); }
        .browse-filter-grid { display: grid; grid-template-columns: minmax(220px, 1.35fr) minmax(190px, .9fr) minmax(180px, .9fr) 150px; gap: 14px; align-items: end; }
        .browse-field { position: relative; }
        .browse-field label, .browse-group-title { display: block; margin-bottom: 7px; font-size: 11px; font-weight: 850; letter-spacing: .12em; text-transform: uppercase; color: #6b7280; }
        .browse-input, .browse-select-button { width: 100%; height: 46px; border: 1px solid #cbd5e1; border-radius: 16px; background: linear-gradient(180deg, #fff 0%, #f8fafc 100%); padding: 0 14px; color: #111827; transition: border-color .18s ease, box-shadow .18s ease, opacity .18s ease, transform .18s ease; box-shadow: inset 0 1px 0 rgba(255,255,255,.8), 0 1px 2px rgba(15,23,42,.04); }
        .browse-input:focus, .browse-select-button:focus { outline: none; border-color: #111827; box-shadow: 0 0 0 3px rgba(17, 24, 39, .08), inset 0 1px 0 rgba(255,255,255,.8); }
        .browse-input:disabled { opacity: .55; cursor: not-allowed; background: #f9fafb; }
        .browse-select-button { display: flex; align-items: center; justify-content: space-between; gap: 10px; text-align: left; font-weight: 500; }
        .browse-select-button:hover { border-color: #94a3b8; transform: translateY(-1px); }
        .browse-select-value { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .browse-select-icon { color: #64748b; font-size: 12px; transition: transform .18s ease; }
        .browse-select-button[aria-expanded="true"] .browse-select-icon { transform: rotate(180deg); }
        .browse-select-menu { position: absolute; z-index: 35; top: calc(100% + 8px); left: 0; right: 0; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 18px; background: rgba(255,255,255,.98); box-shadow: 0 24px 60px rgba(15, 23, 42, .18); backdrop-filter: blur(16px); }
        .browse-select-search { width: calc(100% - 16px); height: 38px; margin: 8px; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0 10px; font-size: 14px; }
        .browse-select-options { max-height: 240px; overflow-y: auto; padding: 4px; }
        .browse-select-option { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 10px; border-radius: 12px; padding: 10px 11px; text-align: left; color: #374151; font-size: 14px; font-weight: 760; }
        .browse-select-option:hover, .browse-select-option.is-selected { background: #111827; color: #fff; }
        .browse-suggestions { position: absolute; z-index: 30; top: calc(100% + 6px); left: 0; right: 0; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 16px; background: #fff; box-shadow: 0 20px 45px rgba(15, 23, 42, .16); }
        .browse-suggestions button { display: block; width: 100%; padding: 11px 13px; text-align: left; font-size: 14px; font-weight: 700; color: #374151; }
        .browse-suggestions button:hover { background: #f3f4f6; color: #111827; }
        .browse-advanced-toggle { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 18px; background: #fff; padding: 12px 14px; color: #111827; font-weight: 850; box-shadow: 0 8px 18px rgba(15, 23, 42, .04); }
        .browse-advanced-toggle span { color: #6b7280; font-size: 13px; font-weight: 750; }
        .browse-advanced-icon { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 999px; background: #111827; color: #fff; transition: transform .18s ease; }
        .browse-advanced-toggle[aria-expanded="true"] .browse-advanced-icon { transform: rotate(180deg); }
        .browse-option-panel { display: grid; grid-template-columns: minmax(0, .55fr) minmax(0, 1fr) minmax(0, 1fr); gap: 14px; margin-top: 14px; }
        .browse-group { border: 1px solid #e5e7eb; border-radius: 20px; background: rgba(255,255,255,.78); padding: 14px; }
        .browse-choice-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .browse-token { display: inline-flex; align-items: center; gap: 7px; border: 1px solid #e5e7eb; border-radius: 999px; background: #fff; padding: 8px 10px; color: #374151; font-size: 13px; font-weight: 750; cursor: pointer; }
        .browse-token input { border-color: #9ca3af; color: #111827; }
        .browse-actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 16px; }
        .browse-checks { display: flex; flex-wrap: wrap; gap: 10px; }
        .browse-button-row { display: flex; gap: 10px; align-items: center; }
        .browse-primary { display: inline-flex; align-items: center; justify-content: center; height: 42px; padding: 0 18px; border-radius: 999px; background: #111827; color: #fff; font-weight: 850; font-size: 14px; transition: transform .18s ease, box-shadow .18s ease; }
        .browse-primary:hover { transform: translateY(-1px); box-shadow: 0 12px 25px rgba(17, 24, 39, .18); }
        .browse-clear { color: #6b7280; font-weight: 750; font-size: 14px; }
        .browse-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px; }
        .browse-card { display: block; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 28px; background: #fff; color: inherit; text-decoration: none; box-shadow: 0 16px 35px rgba(15, 23, 42, .06); transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
        .browse-card:hover { transform: translateY(-4px); border-color: #111827; box-shadow: 0 28px 70px rgba(15, 23, 42, .14); }
        .browse-media { position: relative; aspect-ratio: 1 / 1; overflow: hidden; background: radial-gradient(circle at top, #f3f4f6, #e5e7eb); }
        .browse-media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .35s ease; }
        .browse-card:hover .browse-media img { transform: scale(1.035); }
        .browse-initial { height: 100%; display: grid; place-items: center; font-size: 58px; font-weight: 850; color: #9ca3af; }
        .browse-badge { position: absolute; top: 14px; left: 14px; display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; background: rgba(17, 24, 39, .88); color: #fff; padding: 7px 10px; font-size: 12px; font-weight: 800; backdrop-filter: blur(12px); }
        .browse-card-body { padding: 16px; }
        .browse-name { margin: 0; color: #111827; font-size: 18px; line-height: 1.2; font-weight: 850; letter-spacing: -.02em; }
        .browse-handle { margin-top: 4px; color: #6b7280; font-size: 13px; font-weight: 700; }
        .browse-company { margin: 10px 0 0; color: #374151; font-size: 14px; line-height: 1.4; }
        .browse-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .browse-pill { display: inline-flex; align-items: center; gap: 6px; min-height: 28px; border-radius: 999px; background: #f3f4f6; color: #4b5563; padding: 5px 9px; font-size: 12px; font-weight: 750; }
        .browse-empty { border: 1px solid #e5e7eb; border-radius: 28px; background: #fff; padding: 48px 24px; text-align: center; color: #6b7280; box-shadow: 0 16px 35px rgba(15, 23, 42, .05); }
        .browse-pagination { margin-top: 28px; }
        @media (max-width: 1020px) { .browse-filter-grid { grid-template-columns: 1fr 1fr; } .browse-option-panel { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .browse-shell { padding: 28px 16px 56px; } .browse-filter-grid { grid-template-columns: 1fr; } .browse-actions { align-items: stretch; flex-direction: column; } .browse-button-row { justify-content: space-between; width: 100%; } }
    </style>

    <div class="browse-shell" x-data="browseFilters({
        additionalOpen: false,
        countryOptions: @js(collect($countries)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()),
        experienceOptions: @js(collect($experienceOptions)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()),
        sortOptions: @js([
            ['value' => 'newest', 'label' => 'Newest'],
            ['value' => 'oldest', 'label' => 'Oldest'],
            ['value' => 'name', 'label' => 'Name A-Z'],
        ]),
        selectedCountry: @js($filters['country'] ?? ''),
        selectedExperience: @js($filters['experience_level'] ?? ''),
        selectedSort: @js($filters['sort'] ?? 'newest'),
        initialSearch: @js(old('search', $filters['search'] ?? '')),
        searchSuggestions: @js($searchSuggestions),
        initialCity: @js($filters['city'] ?? ''),
    })">
        <div class="browse-filters">
            <form method="GET" action="{{ route('photographers.browse') }}">
                <div class="browse-filter-grid">
                    <div class="browse-field">
                        <label for="search">Username / Name</label>
                        <input id="search" name="search" type="search" class="browse-input" x-model="search" @input="updateSearchSuggestions()" @keydown.escape="searchMatches = []" autocomplete="off" placeholder="Name, @username, company...">
                        <div class="browse-suggestions" x-show="searchMatches.length" @click.outside="searchMatches = []" x-cloak>
                            <template x-for="match in searchMatches" :key="match">
                                <button type="button" @click="selectSearch(match)" x-text="match"></button>
                            </template>
                        </div>
                    </div>
                    <div class="browse-field">
                        <label>Country</label>
                        <input type="hidden" name="country" :value="selects.country.value">
                        <button type="button" class="browse-select-button" @click="toggleSelect('country')" :aria-expanded="selects.country.open.toString()">
                            <span class="browse-select-value" x-text="selectedLabel('country', 'All countries')"></span>
                            <i class="fas fa-chevron-down browse-select-icon"></i>
                        </button>
                        <div class="browse-select-menu" x-show="selects.country.open" @click.outside="closeSelect('country')" x-cloak>
                            <input type="search" class="browse-select-search" x-model="selects.country.search" placeholder="Search countries...">
                            <div class="browse-select-options">
                                <button type="button" class="browse-select-option" :class="{ 'is-selected': selects.country.value === '' }" @click="chooseOption('country', '', 'All countries'); city = ''; citySuggestions = []">All countries</button>
                                <template x-for="option in filteredOptions('country')" :key="option.value">
                                    <button type="button" class="browse-select-option" :class="{ 'is-selected': selects.country.value === option.value }" @click="chooseOption('country', option.value, option.label); city = ''; citySuggestions = []">
                                        <span x-text="option.label"></span>
                                        <small x-text="option.value"></small>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="browse-field">
                        <label for="city">City</label>
                        <input id="city" name="city" type="search" class="browse-input" x-model="city" @input.debounce.250ms="searchCities()" :disabled="!selects.country.value" placeholder="Choose country first">
                        <div class="browse-suggestions" x-show="citySuggestions.length" x-cloak>
                            <template x-for="suggestion in citySuggestions" :key="suggestion.id">
                                <button type="button" @click="selectCity(suggestion)" x-text="suggestion.city"></button>
                            </template>
                        </div>
                    </div>
                    <div class="browse-field">
                        <label>Sort</label>
                        <input type="hidden" name="sort" :value="selects.sort.value">
                        <button type="button" class="browse-select-button" @click="toggleSelect('sort')" :aria-expanded="selects.sort.open.toString()">
                            <span class="browse-select-value" x-text="selectedLabel('sort', 'Newest')"></span>
                            <i class="fas fa-chevron-down browse-select-icon"></i>
                        </button>
                        <div class="browse-select-menu" x-show="selects.sort.open" @click.outside="closeSelect('sort')" x-cloak>
                            <div class="browse-select-options">
                                <template x-for="option in filteredOptions('sort')" :key="option.value">
                                    <button type="button" class="browse-select-option" :class="{ 'is-selected': selects.sort.value === option.value }" @click="chooseOption('sort', option.value, option.label)" x-text="option.label"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="browse-advanced-toggle" @click="additionalOpen = !additionalOpen" :aria-expanded="additionalOpen.toString()">
                    <div>
                        Additional Filters
                        <span>Services, specialties, travel, verification, and experience.</span>
                    </div>
                    <i class="fas fa-chevron-down browse-advanced-icon"></i>
                </button>

                <div class="browse-option-panel" x-show="additionalOpen" x-cloak>
                    <div class="browse-group">
                        <span class="browse-group-title">Experience</span>
                        <input type="hidden" name="experience_level" :value="selects.experience.value">
                        <div class="browse-field">
                            <button type="button" class="browse-select-button" @click="toggleSelect('experience')" :aria-expanded="selects.experience.open.toString()">
                                <span class="browse-select-value" x-text="selectedLabel('experience', 'Any level')"></span>
                                <i class="fas fa-chevron-down browse-select-icon"></i>
                            </button>
                            <div class="browse-select-menu" x-show="selects.experience.open" @click.outside="closeSelect('experience')" x-cloak>
                                <div class="browse-select-options">
                                    <button type="button" class="browse-select-option" :class="{ 'is-selected': selects.experience.value === '' }" @click="chooseOption('experience', '', 'Any level')">Any level</button>
                                    <template x-for="option in filteredOptions('experience')" :key="option.value">
                                        <button type="button" class="browse-select-option" :class="{ 'is-selected': selects.experience.value === option.value }" @click="chooseOption('experience', option.value, option.label)" x-text="option.label"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="browse-choice-grid" style="margin-top: 12px;">
                            <label class="browse-token"><input type="checkbox" name="travel" value="1" @checked(($filters['travel'] ?? '') === '1')> Available for travel</label>
                            <label class="browse-token"><input type="checkbox" name="verified" value="1" @checked(($filters['verified'] ?? '') === '1')> Verified only</label>
                        </div>
                    </div>
                    <div class="browse-group">
                        <span class="browse-group-title">Services</span>
                        <div class="browse-choice-grid">
                            @foreach($serviceOptions as $key => $label)
                                <label class="browse-token"><input type="checkbox" name="services[]" value="{{ $key }}" @checked(in_array($key, $selectedServices, true))> {{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="browse-group">
                        <span class="browse-group-title">Specialties</span>
                        <div class="browse-choice-grid">
                            @foreach($specialtyOptions as $key => $label)
                                <label class="browse-token"><input type="checkbox" name="specialties[]" value="{{ $key }}" @checked(in_array($key, $selectedSpecialties, true))> {{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="browse-actions">
                    <div class="browse-checks"></div>
                    <div class="browse-button-row">
                        <a href="{{ route('photographers.browse') }}" class="browse-clear">Clear</a>
                        <button type="submit" class="browse-primary">Find Photographers</button>
                    </div>
                </div>
            </form>
        </div>

        @if($photographers->count() > 0)
            <div class="browse-grid">
                @foreach($photographers as $photographer)
                    @php
                        $profile = $photographer->photographerProfile;
                        $displayName = $profile?->display_name ?: $photographer->display_name ?: $photographer->name;
                        $photo = $profile?->profile_photo_path ?: $photographer->photographerPortfolioImages->first()?->thumbnail_path;
                        $photoCount = (int) ($photographer->public_photos_count ?? $photographer->photographerPortfolioImages->count());
                        $location = trim(collect([$profile?->location_city, $profile?->location_country_code ?: $profile?->location_country])->filter()->implode(', '));
                    @endphp
                    <a href="{{ route('photographers.show', $photographer->profileRouteIdentifier()) }}" class="browse-card">
                        <div class="browse-media">
                            @if($photo)
                                <img src="{{ asset($photo) }}" alt="{{ $displayName }}">
                            @else
                                <div class="browse-initial">{{ mb_substr($displayName, 0, 1) }}</div>
                            @endif
                            @if($profile?->isVerified())
                                <span class="browse-badge"><i class="fas fa-check"></i> Verified</span>
                            @endif
                        </div>
                        <div class="browse-card-body">
                            <h3 class="browse-name">{{ $displayName }}</h3>
                            <div class="browse-handle">{{ '@' . $photographer->username }}</div>
                            @if($profile?->shouldShowCompanyName())
                                <p class="browse-company">{{ $profile->professional_name }}</p>
                            @endif
                            <div class="browse-meta">
                                @if($location)
                                    <span class="browse-pill"><i class="fas fa-location-dot"></i> {{ $location }}</span>
                                @endif
                                @if($profile?->experience_level)
                                    <span class="browse-pill"><i class="fas fa-camera"></i> {{ ucfirst($profile->experience_level) }}</span>
                                @endif
                                @if($profile?->available_for_travel)
                                    <span class="browse-pill"><i class="fas fa-plane"></i> Travel</span>
                                @endif
                                <span class="browse-pill"><i class="fas fa-image"></i> {{ $photoCount }} {{ $photoCount === 1 ? 'photo' : 'photos' }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="browse-pagination">{{ $photographers->links() }}</div>
        @else
            <div class="browse-empty">
                <strong>No photographers found.</strong>
                <p class="mt-2">Try widening your search or clearing the current filters.</p>
            </div>
        @endif
    </div>

    <script>
        function browseFilters(config) {
            return {
                additionalOpen: config.additionalOpen || false,
                search: config.initialSearch || '',
                searchOptions: config.searchSuggestions || [],
                searchMatches: [],
                selects: {
                    country: { open: false, search: '', value: config.selectedCountry || '', label: '' , options: config.countryOptions || [] },
                    experience: { open: false, search: '', value: config.selectedExperience || '', label: '', options: config.experienceOptions || [] },
                    sort: { open: false, search: '', value: config.selectedSort || 'newest', label: '', options: config.sortOptions || [] },
                },
                city: config.initialCity || '',
                citySuggestions: [],
                init() {
                    Object.keys(this.selects).forEach((key) => {
                        const selected = this.selects[key].options.find((option) => option.value === this.selects[key].value);
                        if (selected) this.selects[key].label = selected.label;
                    });
                },
                toggleSelect(key) {
                    Object.keys(this.selects).forEach((selectKey) => {
                        if (selectKey !== key) this.selects[selectKey].open = false;
                    });
                    this.selects[key].open = !this.selects[key].open;
                    this.selects[key].search = '';
                },
                closeSelect(key) {
                    this.selects[key].open = false;
                },
                chooseOption(key, value, label) {
                    this.selects[key].value = value;
                    this.selects[key].label = label;
                    this.selects[key].open = false;
                    this.selects[key].search = '';
                },
                selectedLabel(key, fallback) {
                    return this.selects[key].label || fallback;
                },
                filteredOptions(key) {
                    const search = this.selects[key].search.toLowerCase();
                    if (!search) return this.selects[key].options;
                    return this.selects[key].options.filter((option) => option.label.toLowerCase().includes(search) || option.value.toLowerCase().includes(search));
                },
                updateSearchSuggestions() {
                    const value = this.search.trim().toLowerCase();
                    if (value.length < 2) {
                        this.searchMatches = [];
                        return;
                    }

                    this.searchMatches = this.searchOptions
                        .filter((option) => option.toLowerCase().includes(value) && option.toLowerCase() !== value)
                        .slice(0, 8);
                },
                selectSearch(match) {
                    this.search = match;
                    this.searchMatches = [];
                },
                async searchCities() {
                    if (!this.selects.country.value || this.city.length < 2) {
                        this.citySuggestions = [];
                        return;
                    }

                    const response = await fetch(`/api/locations?q=${encodeURIComponent(this.city)}&country=${encodeURIComponent(this.selects.country.value)}&limit=8`);
                    const data = await response.json();
                    this.citySuggestions = data.data || [];
                },
                selectCity(suggestion) {
                    this.city = suggestion.city;
                    this.citySuggestions = [];
                },
            };
        }
    </script>
</x-app-layout>
