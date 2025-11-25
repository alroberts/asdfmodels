<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_users' => User::where('is_admin', false)->count(),
            'total_models' => User::where('is_photographer', false)->where('is_admin', false)->count(),
            'total_photographers' => User::where('is_photographer', true)->count(),
            'verified_models' => ModelProfile::whereNotNull('verified_at')->count(),
            'pending_verifications' => \App\Models\ModelVerification::where('status', 'pending')->count(),
        ];

        $recentUsers = User::where('is_admin', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
        ]);
    }
}

