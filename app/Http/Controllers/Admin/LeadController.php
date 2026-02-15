<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::latest()->paginate(10);

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $lead->update([
            'status' => $lead->status === 'new' ? 'contacted' : 'new',
        ]);

        return redirect()->back()->with('success', 'Status zgłoszenia został zmieniony.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->back()->with('success', 'Zgłoszenie zostało usunięte.');
    }
}
