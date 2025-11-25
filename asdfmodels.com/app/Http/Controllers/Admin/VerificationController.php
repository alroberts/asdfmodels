<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelProfile;
use App\Models\ModelVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Display a listing of verification requests.
     */
    public function index(): View
    {
        try {
            $pending = ModelVerification::with(['user.modelProfile', 'reviewer'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $approved = ModelVerification::with(['user.modelProfile', 'reviewer'])
                ->where('status', 'approved')
                ->orderBy('reviewed_at', 'desc')
                ->limit(20)
                ->get();

            $rejected = ModelVerification::with(['user.modelProfile', 'reviewer'])
                ->where('status', 'rejected')
                ->orderBy('reviewed_at', 'desc')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            // If there's an error, return empty collections
            $pending = collect([]);
            $approved = collect([]);
            $rejected = collect([]);
        }

        return view('admin.verification.index', [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ]);
    }

    /**
     * Show a specific verification request.
     */
    public function show(string $id): View
    {
        $verification = ModelVerification::with(['user.modelProfile', 'reviewer'])
            ->findOrFail($id);

        return view('admin.verification.show', [
            'verification' => $verification,
        ]);
    }

    /**
     * Approve a verification request.
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $verification = ModelVerification::findOrFail($id);
        
        if ($verification->status !== 'pending') {
            return back()->withErrors(['verification' => 'This verification has already been processed.']);
        }

        $verification->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Mark model profile as verified
        $profile = ModelProfile::where('user_id', $verification->user_id)->first();
        if ($profile) {
            $profile->update([
                'verified_at' => now(),
                'verified_by' => Auth::id(),
            ]);
        }

        return redirect()->route('admin.verification.index')
            ->with('status', 'Verification approved and model marked as verified.');
    }

    /**
     * Reject a verification request.
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $verification = ModelVerification::findOrFail($id);
        
        if ($verification->status !== 'pending') {
            return back()->withErrors(['verification' => 'This verification has already been processed.']);
        }

        $verification->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.verification.index')
            ->with('status', 'Verification rejected.');
    }
}

