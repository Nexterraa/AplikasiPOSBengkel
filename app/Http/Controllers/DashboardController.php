<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard untuk Role Admin.
     */
    public function admin(): View
    {
        return view('dashboard.admin');
    }

    /**
     * Dashboard untuk Role Kasir.
     */
    public function kasir(): View
    {
        return view('dashboard.kasir');
    }
}

