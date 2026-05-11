@php
    $isPhotographer = auth()->user()?->is_photographer;
    $profileLabel = $isPhotographer ? 'photographer' : 'model';
    $startImmediately = $startImmediately ?? false;
    $mobileUrl = route('verification.mobile', $mobileSession->token);
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($mobileUrl);
    $benefits = $isPhotographer
        ? [
            ['icon' => 'fas fa-shield-check', 'title' => 'Verified profile badge', 'copy' => 'Give models and collaborators a stronger trust signal before they message or book you.'],
            ['icon' => 'fas fa-building', 'title' => 'Company branding', 'copy' => 'Unlock verified-only company presentation such as logo display and company-name options.'],
            ['icon' => 'fas fa-signature', 'title' => 'Expanded display names', 'copy' => 'Use full-name or company-name profile display options once verification is approved.'],
            ['icon' => 'fas fa-star', 'title' => 'Better platform confidence', 'copy' => 'Verified accounts help us keep ASDF Models safer and easier to trust as the community grows.'],
        ]
        : [
            ['icon' => 'fas fa-shield-check', 'title' => 'Verified profile badge', 'copy' => 'Help photographers quickly understand that your profile has been reviewed.'],
            ['icon' => 'fas fa-signature', 'title' => 'Expanded display names', 'copy' => 'Unlock verified-only name display options, including full-name display when appropriate.'],
            ['icon' => 'fas fa-images', 'title' => 'Stronger portfolio trust', 'copy' => 'Give galleries, polaroids, and credited work more credibility with viewers.'],
            ['icon' => 'fas fa-star', 'title' => 'Future premium access', 'copy' => 'Verification will form part of future profile quality and premium visibility features.'],
        ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-gray-500">Profile Verification</p>
            <h2 class="text-2xl font-semibold leading-tight text-gray-900">Build Trust With Verification</h2>
            <p class="max-w-3xl text-sm text-gray-600">
                Verification combines ID capture with a short liveness check so members can trust who they are working with.
            </p>
        </div>
    </x-slot>

    <div class="py-10" x-data="verificationFlow()" x-init="init()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if($verification && $verification->status === 'pending')
                <div class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 text-yellow-700">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-900">Verification Request Pending</h3>
                            <p class="mt-2 text-sm leading-6 text-yellow-800">
                                Your request is currently under review. Approvals can take up to 72 hours because each submission is checked manually.
                            </p>
                            <p class="mt-2 text-xs font-medium uppercase tracking-wide text-yellow-700">
                                Submitted: {{ $verification->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($verification && $verification->status === 'rejected')
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-700">
                            <i class="fas fa-circle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-red-900">Verification Request Rejected</h3>
                            @if($verification->rejection_reason)
                                <p class="mt-2 text-sm leading-6 text-red-800"><strong>Reason:</strong> {{ $verification->rejection_reason }}</p>
                            @endif
                            <p class="mt-2 text-sm leading-6 text-red-800">You can submit a new verification request below.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid gap-8 {{ $startImmediately ? '' : 'lg:grid-cols-[minmax(0,1fr)_390px]' }}">
                <main class="space-y-8">
                    @unless($startImmediately)
                    <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                        <div class="bg-black px-6 py-8 text-white sm:px-8">
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-gray-300">Why Verify?</p>
                            <h3 class="mt-3 text-3xl font-semibold tracking-tight">A stronger profile, a safer community.</h3>
                            <p class="mt-4 max-w-2xl text-sm leading-6 text-gray-300">
                                Verification is not just a badge. It helps set expectations, reduces uncertainty, and gives serious members more confidence when starting conversations or collaborations.
                            </p>
                        </div>

                        <div class="grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
                            @foreach($benefits as $benefit)
                                <div class="rounded-2xl border border-gray-200 p-5">
                                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-800">
                                        <i class="{{ $benefit['icon'] }}"></i>
                                    </div>
                                    <h4 class="text-base font-semibold text-gray-900">{{ $benefit['title'] }}</h4>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ $benefit['copy'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-500">{{ ucfirst($profileLabel) }} Verification</p>
                                <h3 class="mt-2 text-2xl font-semibold text-gray-900">Ready when you are.</h3>
                                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600">
                                    The verification flow takes you through each step one at a time: choose device, capture ID, record liveness, review, then submit.
                                </p>
                            </div>
                            <a href="{{ route('verification.start') }}" class="inline-flex items-center justify-center rounded-full bg-black px-6 py-4 text-sm font-semibold text-white transition hover:bg-gray-800">
                                <i class="fas fa-shield-check mr-2"></i>
                                Start Verification
                            </a>
                        </div>
                    </section>
                    @endunless

                    <form method="POST" action="{{ route('verification.store') }}" enctype="multipart/form-data" @submit.prevent="submitVerification($event)" x-show="started" x-transition class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        @csrf
                        <input type="hidden" name="verification_type" value="id_upload">
                        <input type="hidden" name="mobile_session_token" value="{{ $mobileSession->token }}">
                        <input type="hidden" name="liveness_code" value="{{ $livenessCode }}">

                        <div class="mb-8">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-500">{{ ucfirst($profileLabel) }} Verification</p>
                            <h3 class="mt-2 text-2xl font-semibold text-gray-900" x-text="stepTitle()"></h3>
                            <p class="mt-3 text-sm leading-6 text-gray-600" x-text="stepDescription()"></p>
                            <div class="mt-5 flex flex-wrap gap-2">
                                <template x-for="step in steps" :key="step.key">
                                    <button type="button" @click="goToStep(step.key)" class="inline-flex items-center rounded-full px-4 py-2 text-xs font-semibold transition" :class="currentStep === step.key ? 'bg-black text-white' : stepComplete(step.key) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'">
                                        <span x-text="step.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <section x-show="currentStep === 'method'" x-transition class="rounded-2xl border border-gray-200 p-5">
                            <div class="grid gap-4 md:grid-cols-2">
                                <button type="button" @click="chooseMethod('desktop')" class="rounded-2xl border p-5 text-left transition hover:border-black hover:bg-gray-50" :class="captureMethod === 'desktop' ? 'border-black bg-gray-50' : 'border-gray-200'">
                                    <i class="fas fa-laptop mb-4 text-2xl text-gray-800"></i>
                                    <h4 class="font-semibold text-gray-900">Use this device</h4>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Use your current webcam and microphone, or upload the ID document from this computer.</p>
                                </button>
                                <button type="button" @click="chooseMethod('mobile')" class="rounded-2xl border p-5 text-left transition hover:border-black hover:bg-gray-50" :class="captureMethod === 'mobile' ? 'border-black bg-gray-50' : 'border-gray-200'">
                                    <i class="fas fa-mobile-screen mb-4 text-2xl text-gray-800"></i>
                                    <h4 class="font-semibold text-gray-900">Continue on mobile</h4>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Best if this device has no webcam. Scan a QR code or share a secure link.</p>
                                </button>
                            </div>
                            <div class="mt-6 flex justify-end">
                                <button type="button" @click="currentStep = captureMethod === 'mobile' ? 'mobile' : 'id'" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white">Continue</button>
                            </div>
                        </section>

                            <section x-show="currentStep === 'id'" x-transition class="rounded-2xl border border-gray-200 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Part 1</p>
                                        <h4 class="mt-1 text-lg font-semibold text-gray-900">Scan Your ID</h4>
                                        <p class="mt-2 text-sm leading-6 text-gray-600">Use your webcam to capture the ID, or upload a clear PDF/JPG/PNG document.</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="idReady ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" x-text="idReady ? 'Ready' : 'Needed'"></span>
                                </div>

                                <div class="mt-5 overflow-hidden rounded-2xl bg-gray-100">
                                    <video x-ref="idVideo" x-show="idCameraActive && !idPreviewUrl" autoplay playsinline muted class="aspect-video w-full object-cover"></video>
                                    <img x-show="idPreviewUrl" :src="idPreviewUrl" alt="ID preview" class="aspect-video w-full object-contain">
                                    <div x-show="!idCameraActive && !idPreviewUrl" class="flex aspect-video items-center justify-center text-gray-400">
                                        <i class="fas fa-id-card text-5xl"></i>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-3">
                                    <button type="button" @click="startIdCamera()" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        <i class="fas fa-camera mr-2"></i>Start Camera
                                    </button>
                                    <button type="button" @click="captureId()" :disabled="!idCameraActive" class="rounded-md bg-black px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50">
                                        Capture ID
                                    </button>
                                    <label class="cursor-pointer rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        Upload ID
                                        <input type="file" name="id_document" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="handleIdUpload($event)">
                                    </label>
                                </div>
                                <div class="mt-6 flex justify-between">
                                    <button type="button" @click="currentStep = 'method'" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Back</button>
                                    <button type="button" @click="currentStep = 'liveness'" :disabled="!idReady" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Continue</button>
                                </div>
                            </section>

                            <section x-show="currentStep === 'liveness'" x-transition class="rounded-2xl border border-gray-200 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Part 2</p>
                                        <h4 class="mt-1 text-lg font-semibold text-gray-900">Liveness Verification</h4>
                                        <p class="mt-2 text-sm leading-6 text-gray-600">Record a video within 10 seconds and read this number clearly:</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="livenessReady ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" x-text="livenessReady ? 'Ready' : 'Needed'"></span>
                                </div>

                                <div class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-5 text-center">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-500">Read this code</p>
                                    <p class="mt-2 font-mono text-4xl font-black tracking-[0.22em] text-gray-950">{{ $livenessCode }}</p>
                                </div>

                                <div class="mt-5 overflow-hidden rounded-2xl bg-gray-100">
                                    <video x-ref="liveVideo" x-show="livenessCameraActive && !livenessPreviewUrl" autoplay playsinline muted class="aspect-video w-full object-cover"></video>
                                    <video x-show="livenessPreviewUrl" :src="livenessPreviewUrl" controls class="aspect-video w-full object-cover"></video>
                                    <div x-show="!livenessCameraActive && !livenessPreviewUrl" class="flex aspect-video items-center justify-center text-gray-400">
                                        <i class="fas fa-video text-5xl"></i>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-3">
                                    <button type="button" @click="startLivenessCamera()" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        <i class="fas fa-video mr-2"></i>Start Camera
                                    </button>
                                    <button type="button" @click="recordLiveness()" :disabled="!livenessCameraActive || recording" class="rounded-md bg-black px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50">
                                        <span x-text="recording ? `Recording ${recordingSeconds}s` : 'Record 10s Video'"></span>
                                    </button>
                                </div>
                                <div class="mt-6 flex justify-between">
                                    <button type="button" @click="currentStep = 'id'" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Back</button>
                                    <button type="button" @click="currentStep = 'review'" :disabled="!livenessReady" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Review</button>
                                </div>
                            </section>

                        <section x-show="currentStep === 'mobile'" x-transition class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h4 class="font-semibold text-blue-950">Continue on mobile</h4>
                                    <p class="mt-1 text-sm leading-6 text-blue-900">Scan the QR code or share the secure link. This page will update automatically once mobile capture is complete.</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button type="button" @click="copyMobileLink()" class="rounded-md border border-blue-200 bg-white px-3 py-2 text-sm font-semibold text-blue-900">Copy Link</button>
                                        <a href="{{ $mobileUrl }}" target="_blank" class="rounded-md border border-blue-200 bg-white px-3 py-2 text-sm font-semibold text-blue-900">Open Link</a>
                                    </div>
                                </div>
                                <img src="{{ $qrUrl }}" alt="Mobile verification QR code" class="h-36 w-36 rounded-2xl border border-blue-200 bg-white p-2">
                            </div>
                            <div x-show="waitingForMobile" class="mt-4 rounded-xl border border-blue-200 bg-white p-4 text-sm text-blue-950">
                                Complete the verification on your other device before continuing here.
                            </div>
                            <div x-show="mobileComplete" class="mt-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-900">
                                Mobile capture received. You can submit this verification request now.
                            </div>
                            <div class="mt-6 flex justify-between">
                                <button type="button" @click="currentStep = 'method'" class="rounded-md border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-900">Back</button>
                                <button type="button" @click="currentStep = 'review'" :disabled="!mobileComplete" class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Review</button>
                            </div>
                        </section>

                        <section x-show="currentStep === 'review'" x-transition class="rounded-2xl border border-gray-200 p-5">
                            <h4 class="font-semibold text-gray-900">Review Before Submitting</h4>
                            <p class="mt-2 text-sm leading-6 text-gray-600">Nothing is sent until you press submit. Make sure the ID is readable and your liveness video clearly shows you reading the number.</p>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <p class="text-sm font-semibold text-gray-900">ID capture</p>
                                    <p class="mt-1 text-sm" :class="idReady || mobileComplete ? 'text-green-700' : 'text-red-700'" x-text="idReady || mobileComplete ? 'Ready' : 'Missing'"></p>
                                </div>
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <p class="text-sm font-semibold text-gray-900">Liveness video</p>
                                    <p class="mt-1 text-sm" :class="livenessReady || mobileComplete ? 'text-green-700' : 'text-red-700'" x-text="livenessReady || mobileComplete ? 'Ready' : 'Missing'"></p>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-between">
                                <button type="button" @click="currentStep = mobileComplete ? 'mobile' : 'liveness'" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Back</button>
                                <button type="submit" :disabled="submitting || (!mobileComplete && (!idReady || !livenessReady))" class="inline-flex items-center justify-center rounded-md bg-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50">
                                    <i x-show="submitting" class="fas fa-spinner fa-spin mr-2"></i>
                                    <i x-show="!submitting" class="fas fa-shield-check mr-2"></i>
                                    <span x-text="submitting ? 'Submitting...' : 'Submit Verification'"></span>
                                </button>
                            </div>
                        </section>

                        <div x-show="errorMessage" class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" x-text="errorMessage"></div>
                        <x-input-error :messages="$errors->get('verification')" class="mt-4" />
                    </form>
                </main>

                @unless($startImmediately)
                <aside class="space-y-6">
                    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">How Review Works</h3>
                        <div class="mt-5 space-y-4">
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Step 1</p>
                                <p class="mt-1 font-semibold text-gray-900">ID capture</p>
                                <p class="mt-1 text-sm text-gray-600">We need a clear view of your ID or uploaded document.</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Step 2</p>
                                <p class="mt-1 font-semibold text-gray-900">Liveness video</p>
                                <p class="mt-1 text-sm text-gray-600">Read the random number in a video recorded within 10 seconds.</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Step 3</p>
                                <p class="mt-1 font-semibold text-gray-900">Manual review</p>
                                <p class="mt-1 text-sm text-gray-600">Approvals can take up to 72 hours.</p>
                            </div>
                        </div>
                    </section>
                </aside>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function verificationFlow() {
        return {
            mobileUrl: @json($mobileUrl),
            statusUrl: @json(route('verification.mobile.status', $mobileSession->token)),
            started: @json($startImmediately),
            currentStep: 'method',
            captureMethod: 'desktop',
            steps: [
                { key: 'method', label: 'Choose' },
                { key: 'id', label: 'ID' },
                { key: 'liveness', label: 'Liveness' },
                { key: 'mobile', label: 'Mobile' },
                { key: 'review', label: 'Review' },
            ],
            idStream: null,
            livenessStream: null,
            idCameraActive: false,
            livenessCameraActive: false,
            idBlob: null,
            idPreviewUrl: '',
            livenessBlob: null,
            livenessPreviewUrl: '',
            livenessReady: false,
            idReady: false,
            recording: false,
            recordingSeconds: 10,
            mobileComplete: @json($mobileSession->isComplete()),
            waitingForMobile: false,
            submitting: false,
            errorMessage: '',

            init() {
                this.pollMobileStatus();
                setInterval(() => this.pollMobileStatus(), 4000);
            },

            startVerification() {
                this.started = true;
                this.currentStep = this.mobileComplete ? 'review' : 'method';
                this.$nextTick(() => {
                    document.querySelector('form[action="{{ route('verification.store') }}"]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },

            chooseMethod(method) {
                this.captureMethod = method;
                this.waitingForMobile = method === 'mobile';
            },

            stepTitle() {
                return {
                    method: 'How would you like to verify?',
                    id: 'Scan or upload your ID',
                    liveness: 'Record your liveness check',
                    mobile: 'Continue on your mobile device',
                    review: 'Review and submit',
                }[this.currentStep] || 'Verification';
            },

            stepDescription() {
                return {
                    method: 'Choose whether to complete verification on this device or continue on mobile.',
                    id: 'Capture your ID with the webcam or upload a clear document.',
                    liveness: 'Read the random number aloud in a video recorded within 10 seconds.',
                    mobile: 'Scan the QR code or share the link, then finish capture on your mobile device.',
                    review: 'Check that both required parts are ready before submitting for manual review.',
                }[this.currentStep] || '';
            },

            stepComplete(step) {
                if (step === 'method') {
                    return this.started;
                }
                if (step === 'id') {
                    return this.idReady || this.mobileComplete;
                }
                if (step === 'liveness') {
                    return this.livenessReady || this.mobileComplete;
                }
                if (step === 'mobile') {
                    return this.mobileComplete;
                }
                if (step === 'review') {
                    return this.mobileComplete || (this.idReady && this.livenessReady);
                }
                return false;
            },

            goToStep(step) {
                if (step === 'review' && !this.stepComplete('review')) {
                    return;
                }
                if (this.captureMethod === 'mobile' && ['id', 'liveness'].includes(step)) {
                    this.currentStep = 'mobile';
                    return;
                }
                this.currentStep = step;
            },

            async startIdCamera() {
                this.errorMessage = '';
                try {
                    this.idStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                    this.$refs.idVideo.srcObject = this.idStream;
                    this.idCameraActive = true;
                } catch (error) {
                    this.waitingForMobile = true;
                    this.errorMessage = 'Camera access was not available. Use the mobile QR/link option instead.';
                }
            },

            captureId() {
                const video = this.$refs.idVideo;
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth || 1280;
                canvas.height = video.videoHeight || 720;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => {
                    this.idBlob = blob;
                    this.idPreviewUrl = URL.createObjectURL(blob);
                    this.idReady = true;
                    this.stopStream('id');
                }, 'image/jpeg', 0.92);
            },

            handleIdUpload(event) {
                const file = event.target.files[0];
                if (!file) {
                    return;
                }
                this.idBlob = null;
                this.idPreviewUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : '';
                this.idReady = true;
            },

            async startLivenessCamera() {
                this.errorMessage = '';
                try {
                    this.livenessStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                    this.$refs.liveVideo.srcObject = this.livenessStream;
                    this.livenessCameraActive = true;
                } catch (error) {
                    this.waitingForMobile = true;
                    this.errorMessage = 'Camera or microphone access was not available. Use the mobile QR/link option instead.';
                }
            },

            recordLiveness() {
                if (!this.livenessStream || this.recording) {
                    return;
                }

                const recorder = new MediaRecorder(this.livenessStream, { mimeType: MediaRecorder.isTypeSupported('video/webm') ? 'video/webm' : '' });
                const chunks = [];
                this.recording = true;
                this.recordingSeconds = 10;
                this.livenessReady = false;

                recorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        chunks.push(event.data);
                    }
                };

                recorder.onstop = () => {
                    this.livenessBlob = new Blob(chunks, { type: chunks[0]?.type || 'video/webm' });
                    this.livenessPreviewUrl = URL.createObjectURL(this.livenessBlob);
                    this.livenessReady = true;
                    this.recording = false;
                    this.stopStream('liveness');
                };

                recorder.start();
                const countdown = setInterval(() => {
                    this.recordingSeconds -= 1;
                    if (this.recordingSeconds <= 0) {
                        clearInterval(countdown);
                    }
                }, 1000);
                setTimeout(() => {
                    if (recorder.state !== 'inactive') {
                        recorder.stop();
                    }
                }, 10000);
            },

            stopStream(type) {
                const stream = type === 'id' ? this.idStream : this.livenessStream;
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                }
                if (type === 'id') {
                    this.idCameraActive = false;
                    this.idStream = null;
                    return;
                }
                this.livenessCameraActive = false;
                this.livenessStream = null;
            },

            async pollMobileStatus() {
                try {
                    const response = await fetch(this.statusUrl, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        return;
                    }
                    const data = await response.json();
                    if (data.complete) {
                        this.mobileComplete = true;
                        this.waitingForMobile = false;
                    }
                } catch (error) {
                    // Polling is convenience only.
                }
            },

            async copyMobileLink() {
                this.waitingForMobile = true;
                await navigator.clipboard.writeText(this.mobileUrl);
            },

            async submitVerification(event) {
                this.errorMessage = '';
                if (!this.mobileComplete && (!this.idReady || !this.livenessReady)) {
                    this.errorMessage = 'Please complete both ID capture and liveness verification before submitting.';
                    return;
                }

                this.submitting = true;
                const form = event.target;
                const formData = new FormData(form);

                if (!this.mobileComplete) {
                    if (this.idBlob) {
                        formData.set('id_document', this.idBlob, 'id-capture.jpg');
                    }
                    if (this.livenessBlob) {
                        formData.set('liveness_video', this.livenessBlob, 'liveness.webm');
                    }
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { Accept: 'text/html' },
                    });

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    if (!response.ok) {
                        this.errorMessage = 'Unable to submit verification. Please check both captures and try again.';
                        return;
                    }

                    window.location.reload();
                } catch (error) {
                    this.errorMessage = 'Unable to submit verification. Please try again.';
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>
