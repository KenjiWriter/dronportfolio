<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index()
    {
        $leads = \App\Models\Lead::orderBy('created_at', 'desc')->get();
        return \Inertia\Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
        ]);
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Lead $lead)
    {
        $lead->update([
            'status' => $lead->status === 'new' ? 'contacted' : 'new',
        ]);

        return redirect()->back()->with('success', 'Status zgłoszenia został zmieniony.');
    }
}
