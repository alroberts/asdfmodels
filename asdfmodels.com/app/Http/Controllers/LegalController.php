<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    /**
     * Display the Terms of Service page.
     */
    public function terms(): View
    {
        return view('legal.terms');
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy(): View
    {
        return view('legal.privacy');
    }

    /**
     * Display the Cookie Policy page.
     */
    public function cookies(): View
    {
        return view('legal.cookies');
    }
}

