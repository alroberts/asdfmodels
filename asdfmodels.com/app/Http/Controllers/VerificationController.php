<?php

namespace App\Http\Controllers;

use App\Models\ModelVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Show the verification upload form.
     */
    public function create(): View
    {
        $user = Auth::user();
        $verification = ModelVerification::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'rejected'])
            ->latest()
            ->first();

        return view('verification.create', [
            'verification' => $verification,
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
            'id_document' => ['required_if:verification_type,id_upload', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'video' => ['required_if:verification_type,video_identification', 'file', 'mimes:mp4,mov,avi', 'max:51200'], // 50MB
        ]);

        $userFolder = public_path("uploads/models/{$user->id}/verification");
        if (!file_exists($userFolder)) {
            mkdir($userFolder, 0755, true);
        }

        $idDocumentPath = null;
        $videoPath = null;

        if ($request->hasFile('id_document')) {
            $file = $request->file('id_document');
            $filename = 'id_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($userFolder, $filename);
            $idDocumentPath = "uploads/models/{$user->id}/verification/{$filename}";
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $filename = 'video_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($userFolder, $filename);
            $videoPath = "uploads/models/{$user->id}/verification/{$filename}";
        }

        ModelVerification::create([
            'user_id' => $user->id,
            'verification_type' => $validated['verification_type'],
            'id_document_path' => $idDocumentPath,
            'video_path' => $videoPath,
            'status' => 'pending',
        ]);

        return redirect()->route('verification.create')
            ->with('status', 'Verification request submitted. An admin will review it shortly.');
    }
}

