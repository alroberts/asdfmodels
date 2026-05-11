<?php

namespace App\Http\Controllers;

use App\Models\ModelVerification;
use App\Models\VerificationSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Show the verification introduction page.
     */
    public function create(): View
    {
        return $this->verificationView(false);
    }

    /**
     * Show the guided verification flow.
     */
    public function start(): View
    {
        return $this->verificationView(true);
    }

    private function verificationView(bool $startImmediately): View
    {
        $user = Auth::user();
        $verification = ModelVerification::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'rejected'])
            ->latest()
            ->first();
        $mobileSession = VerificationSession::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$mobileSession) {
            $mobileSession = VerificationSession::create([
                'user_id' => $user->id,
                'token' => Str::random(48),
                'liveness_code' => $this->generateLivenessCode(),
                'expires_at' => now()->addHours(2),
            ]);
        }

        return view('verification.create', [
            'verification' => $verification,
            'mobileSession' => $mobileSession,
            'livenessCode' => $mobileSession->liveness_code,
            'startImmediately' => $startImmediately,
        ]);
    }

    /**
     * Store verification documents.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Check if there's already a pending verification
        $existing = ModelVerification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->withErrors(['verification' => 'You already have a pending verification request.']);
        }

        $validated = $request->validate([
            'verification_type' => ['required', 'in:id_upload,video_identification'],
            'mobile_session_token' => ['nullable', 'string'],
            'id_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'liveness_video' => ['nullable', 'file', 'mimes:webm,mp4,mov,avi', 'max:51200'],
            'liveness_code' => ['required', 'string', 'max:20'],
        ]);

        $userFolder = public_path("uploads/models/{$user->id}/verification");
        if (!file_exists($userFolder)) {
            mkdir($userFolder, 0755, true);
        }

        $idDocumentPath = null;
        $livenessVideoPath = null;
        $captureMethod = 'desktop';

        if (!empty($validated['mobile_session_token'])) {
            $mobileSession = VerificationSession::where('token', $validated['mobile_session_token'])
                ->where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->first();

            if ($mobileSession?->isComplete()) {
                $idDocumentPath = $mobileSession->id_document_path;
                $livenessVideoPath = $mobileSession->liveness_video_path;
                $validated['liveness_code'] = $mobileSession->liveness_code;
                $captureMethod = 'mobile_handoff';
            }
        }

        if (!$idDocumentPath && $request->hasFile('id_document')) {
            $file = $request->file('id_document');
            $filename = 'id_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($userFolder, $filename);
            $idDocumentPath = "uploads/models/{$user->id}/verification/{$filename}";
        }

        if (!$livenessVideoPath && $request->hasFile('liveness_video')) {
            $file = $request->file('liveness_video');
            $filename = 'liveness_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($userFolder, $filename);
            $livenessVideoPath = "uploads/models/{$user->id}/verification/{$filename}";
        }

        if (!$idDocumentPath || !$livenessVideoPath) {
            return back()
                ->withErrors(['verification' => 'Please provide both ID capture/document and the 10-second liveness video before submitting.'])
                ->withInput();
        }

        ModelVerification::create([
            'user_id' => $user->id,
            'verification_type' => $validated['verification_type'],
            'id_document_path' => $idDocumentPath,
            'video_path' => $livenessVideoPath,
            'liveness_video_path' => $livenessVideoPath,
            'liveness_code' => $validated['liveness_code'],
            'capture_method' => $captureMethod,
            'status' => 'pending',
        ]);

        return redirect()->route('verification.create')
            ->with('status', 'Verification request submitted. Reviews are completed manually and can take up to 72 hours.');
    }

    public function mobile(string $token): View
    {
        $mobileSession = VerificationSession::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('verification.mobile', [
            'mobileSession' => $mobileSession,
            'livenessCode' => $mobileSession->liveness_code,
        ]);
    }

    public function mobileStore(Request $request, string $token): JsonResponse
    {
        $mobileSession = VerificationSession::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $validated = $request->validate([
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'liveness_video' => ['required', 'file', 'mimes:webm,mp4,mov,avi', 'max:51200'],
        ]);

        $userFolder = public_path("uploads/models/{$mobileSession->user_id}/verification/mobile");
        if (!file_exists($userFolder)) {
            mkdir($userFolder, 0755, true);
        }

        $idFile = $validated['id_document'];
        $idFilename = 'id_mobile_' . uniqid() . '.' . $idFile->getClientOriginalExtension();
        $idFile->move($userFolder, $idFilename);

        $videoFile = $validated['liveness_video'];
        $videoFilename = 'liveness_mobile_' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
        $videoFile->move($userFolder, $videoFilename);

        $mobileSession->update([
            'id_document_path' => "uploads/models/{$mobileSession->user_id}/verification/mobile/{$idFilename}",
            'liveness_video_path' => "uploads/models/{$mobileSession->user_id}/verification/mobile/{$videoFilename}",
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Mobile verification capture received. You can return to your original device.',
            'status' => 'complete',
        ]);
    }

    public function mobileStatus(string $token): JsonResponse
    {
        $user = Auth::user();
        $mobileSession = VerificationSession::where('token', $token)
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return response()->json([
            'complete' => $mobileSession->isComplete(),
            'completed_at' => optional($mobileSession->completed_at)->toIso8601String(),
        ]);
    }

    private function generateLivenessCode(): string
    {
        return collect(range(1, 6))
            ->map(fn () => (string) random_int(0, 9))
            ->implode('');
    }
}
