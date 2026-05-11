<x-guest-layout>
    <div class="min-h-screen bg-gray-100 px-4 py-6" x-data="mobileVerification()" x-init="init()">
        <div class="mx-auto max-w-lg overflow-hidden rounded-3xl bg-white shadow-xl">
            <div class="bg-black px-6 py-6 text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-300">ASDF Models</p>
                <h1 class="mt-2 text-2xl font-semibold">Mobile Verification Capture</h1>
                <p class="mt-2 text-sm leading-6 text-gray-300">Capture your ID and a short liveness video, then return to your original device.</p>
            </div>

            <div class="space-y-6 p-5">
                @if($mobileSession->isComplete())
                    <div class="rounded-2xl border border-green-200 bg-green-50 p-5 text-green-900">
                        <h2 class="font-semibold">Capture already received</h2>
                        <p class="mt-2 text-sm">You can return to your original device and submit the verification request.</p>
                    </div>
                @else
                    <section class="rounded-2xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Part 1</p>
                                <h2 class="font-semibold text-gray-900">Scan Your ID</h2>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="idReady ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" x-text="idReady ? 'Ready' : 'Needed'"></span>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-2xl bg-gray-100">
                            <video x-ref="idVideo" x-show="idCameraActive && !idPreviewUrl" autoplay playsinline muted class="aspect-video w-full object-cover"></video>
                            <img x-show="idPreviewUrl" :src="idPreviewUrl" alt="ID preview" class="aspect-video w-full object-contain">
                            <div x-show="!idCameraActive && !idPreviewUrl" class="flex aspect-video items-center justify-center text-gray-400">
                                <i class="fas fa-id-card text-4xl"></i>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" @click="startIdCamera()" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold">Start Camera</button>
                            <button type="button" @click="captureId()" :disabled="!idCameraActive" class="rounded-md bg-black px-3 py-2 text-sm font-semibold text-white disabled:opacity-50">Capture ID</button>
                            <label class="cursor-pointer rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold">
                                Upload
                                <input type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="handleIdUpload($event)">
                            </label>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Part 2</p>
                                <h2 class="font-semibold text-gray-900">Liveness Video</h2>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="livenessReady ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" x-text="livenessReady ? 'Ready' : 'Needed'"></span>
                        </div>

                        <div class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Read this code</p>
                            <p class="mt-2 font-mono text-4xl font-black tracking-[0.18em] text-black">{{ $livenessCode }}</p>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-2xl bg-gray-100">
                            <video x-ref="liveVideo" x-show="livenessCameraActive && !livenessPreviewUrl" autoplay playsinline muted class="aspect-video w-full object-cover"></video>
                            <video x-show="livenessPreviewUrl" :src="livenessPreviewUrl" controls class="aspect-video w-full object-cover"></video>
                            <div x-show="!livenessCameraActive && !livenessPreviewUrl" class="flex aspect-video items-center justify-center text-gray-400">
                                <i class="fas fa-video text-4xl"></i>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" @click="startLivenessCamera()" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold">Start Camera</button>
                            <button type="button" @click="recordLiveness()" :disabled="!livenessCameraActive || recording" class="rounded-md bg-black px-3 py-2 text-sm font-semibold text-white disabled:opacity-50">
                                <span x-text="recording ? `Recording ${recordingSeconds}s` : 'Record 10s Video'"></span>
                            </button>
                        </div>
                    </section>

                    <div x-show="errorMessage" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" x-text="errorMessage"></div>
                    <div x-show="successMessage" class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900" x-text="successMessage"></div>

                    <button type="button" @click="submitMobile()" :disabled="submitting || !idReady || !livenessReady" class="inline-flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-semibold text-white disabled:opacity-50">
                        <i x-show="submitting" class="fas fa-spinner fa-spin mr-2"></i>
                        <span x-text="submitting ? 'Sending...' : 'Send Capture'"></span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</x-guest-layout>

<script>
    function mobileVerification() {
        return {
            submitUrl: @json(route('verification.mobile.store', $mobileSession->token)),
            csrf: @json(csrf_token()),
            idStream: null,
            livenessStream: null,
            idCameraActive: false,
            livenessCameraActive: false,
            idBlob: null,
            idPreviewUrl: '',
            livenessBlob: null,
            livenessPreviewUrl: '',
            idReady: false,
            livenessReady: false,
            recording: false,
            recordingSeconds: 10,
            submitting: false,
            errorMessage: '',
            successMessage: '',

            init() {},

            async startIdCamera() {
                try {
                    this.idStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                    this.$refs.idVideo.srcObject = this.idStream;
                    this.idCameraActive = true;
                } catch (error) {
                    this.errorMessage = 'Camera access was not available. You can upload an ID image/document instead.';
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
                this.idBlob = file;
                this.idPreviewUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : '';
                this.idReady = true;
            },

            async startLivenessCamera() {
                try {
                    this.livenessStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                    this.$refs.liveVideo.srcObject = this.livenessStream;
                    this.livenessCameraActive = true;
                } catch (error) {
                    this.errorMessage = 'Camera or microphone access was not available.';
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

            async submitMobile() {
                this.errorMessage = '';
                this.successMessage = '';
                this.submitting = true;
                const formData = new FormData();
                formData.append('_token', this.csrf);
                formData.append('id_document', this.idBlob, this.idBlob.name || 'id-capture.jpg');
                formData.append('liveness_video', this.livenessBlob, 'liveness.webm');

                try {
                    const response = await fetch(this.submitUrl, {
                        method: 'POST',
                        body: formData,
                        headers: { Accept: 'application/json' },
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        this.errorMessage = payload.message || 'Unable to send capture. Please try again.';
                        return;
                    }
                    this.successMessage = payload.message || 'Capture received. Return to your original device.';
                } catch (error) {
                    this.errorMessage = 'Unable to send capture. Please try again.';
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>
