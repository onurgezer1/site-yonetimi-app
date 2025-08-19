<?php

namespace App\Http\Controllers;

use App\Models\Aidat;
use App\Models\Site;
use App\Services\AidatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AidatController extends Controller
{
    public function __construct(
        protected AidatService $aidatService
    ) {}

    /**
     * Display a listing of aidats.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Aidat::with(['site', 'apartment', 'user']);
        
        // Filter based on user role
        if ($user->role === 'sakin') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'site_yoneticisi') {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('manager_user_id', $user->id);
            });
        }
        
        // Add filters
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->input('site_id'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('month') && $request->filled('year')) {
            $query->forPeriod($request->input('month'), $request->input('year'));
        }
        
        $aidats = $query->orderBy('due_date', 'desc')->paginate(20);
        
        // Get available sites for filter
        $sitesQuery = Site::query();
        if ($user->role === 'site_yoneticisi') {
            $sitesQuery->where('manager_user_id', $user->id);
        } elseif ($user->role === 'sakin') {
            $sitesQuery->where('id', $user->site_id);
        }
        $sites = $sitesQuery->get();
        
        return view('aidats.index', compact('aidats', 'sites'));
    }

    /**
     * Show the form for creating a new aidat.
     */
    public function create()
    {
        $this->authorize('create', Aidat::class);
        
        $user = Auth::user();
        $sitesQuery = Site::with('apartments');
        
        if ($user->role === 'site_yoneticisi') {
            $sitesQuery->where('manager_user_id', $user->id);
        }
        
        $sites = $sitesQuery->get();
        
        return view('aidats.create', compact('sites'));
    }

    /**
     * Store a newly created aidat.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Aidat::class);
        
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'apartment_id' => 'required|exists:apartments,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2024|max:2030',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $apartment = \App\Models\Apartment::findOrFail($validated['apartment_id']);
            
            $aidat = $this->aidatService->generateApartmentAidat(
                $apartment,
                $validated['month'],
                $validated['year'],
                $validated['amount']
            );
            
            if ($request->filled('notes')) {
                $aidat->update(['notes' => $validated['notes']]);
            }
            
            return redirect()
                ->route('aidats.show', $aidat)
                ->with('success', 'Aidat kaydı başarıyla oluşturuldu.');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified aidat.
     */
    public function show(Aidat $aidat)
    {
        $this->authorize('view', $aidat);
        
        $aidat->load(['site', 'apartment', 'user', 'payment']);
        
        return view('aidats.show', compact('aidat'));
    }

    /**
     * Show the form for editing the specified aidat.
     */
    public function edit(Aidat $aidat)
    {
        $this->authorize('update', $aidat);
        
        return view('aidats.edit', compact('aidat'));
    }

    /**
     * Update the specified aidat.
     */
    public function update(Request $request, Aidat $aidat)
    {
        $this->authorize('update', $aidat);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $aidat->update($validated);
        $aidat->updateAmounts(); // Recalculate totals
        
        return redirect()
            ->route('aidats.show', $aidat)
            ->with('success', 'Aidat kaydı başarıyla güncellendi.');
    }

    /**
     * Remove the specified aidat.
     */
    public function destroy(Aidat $aidat)
    {
        $this->authorize('delete', $aidat);
        
        if ($aidat->status === Aidat::STATUS_PAID) {
            return back()->with('error', 'Ödenmiş aidat kayıtları silinemez.');
        }
        
        $aidat->delete();
        
        return redirect()
            ->route('aidats.index')
            ->with('success', 'Aidat kaydı başarıyla silindi.');
    }

    /**
     * Generate monthly aidats for a site.
     */
    public function generateMonthly(Request $request)
    {
        $this->authorize('create', Aidat::class);
        
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2024|max:2030',
        ]);
        
        try {
            $site = Site::findOrFail($validated['site_id']);
            
            $aidats = $this->aidatService->generateMonthlyAidat(
                $site,
                $validated['month'],
                $validated['year']
            );
            
            return response()->json([
                'success' => true,
                'message' => count($aidats) . ' adet aidat kaydı oluşturuldu.',
                'data' => $aidats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Calculate late fee for an aidat.
     */
    public function calculateLateFee(Aidat $aidat)
    {
        $this->authorize('view', $aidat);
        
        $lateFee = $this->aidatService->calculateLateFee($aidat);
        
        return response()->json([
            'late_fee' => $lateFee,
            'total_amount' => $aidat->amount + $lateFee,
        ]);
    }
}