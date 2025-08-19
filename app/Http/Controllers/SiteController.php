<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Apartment;
use App\Services\SiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function __construct(
        protected SiteService $siteService
    ) {}

    /**
     * Display a listing of sites.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Site::with(['manager', 'apartments']);
        
        // Filter sites based on user role
        if ($user->role === 'sakin') {
            $query->where('id', $user->site_id);
        } elseif ($user->role === 'site_yoneticisi') {
            $query->where('manager_user_id', $user->id);
        }
        
        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }
        
        $sites = $query->paginate(15);
        
        return view('sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new site.
     */
    public function create()
    {
        $this->authorize('create', Site::class);
        
        return view('sites.create');
    }

    /**
     * Store a newly created site.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Site::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'tax_number' => 'nullable|string|max:20',
            'total_apartments' => 'required|integer|min:1|max:1000',
            'total_area' => 'required|numeric|min:0',
            'common_area_ratio' => 'required|numeric|min:0|max:100',
        ]);
        
        $site = $this->siteService->createSite($validated, Auth::user());
        
        return redirect()
            ->route('sites.show', $site)
            ->with('success', 'Site başarıyla oluşturuldu.');
    }

    /**
     * Display the specified site.
     */
    public function show(Site $site)
    {
        $this->authorize('view', $site);
        
        $site->load(['manager', 'apartments.residents', 'aidats', 'payments']);
        
        $statistics = [
            'total_apartments' => $site->apartments->count(),
            'occupied_apartments' => $site->apartments->where('is_occupied', true)->count(),
            'total_residents' => $site->residents()->count(),
            'monthly_income' => $site->total_monthly_aidat,
            'occupancy_rate' => $site->occupancy_rate,
            'pending_payments' => $site->aidats()->pending()->count(),
            'overdue_payments' => $site->aidats()->late()->count(),
        ];
        
        return view('sites.show', compact('site', 'statistics'));
    }

    /**
     * Show the form for editing the specified site.
     */
    public function edit(Site $site)
    {
        $this->authorize('update', $site);
        
        return view('sites.edit', compact('site'));
    }

    /**
     * Update the specified site.
     */
    public function update(Request $request, Site $site)
    {
        $this->authorize('update', $site);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'tax_number' => 'nullable|string|max:20',
            'total_apartments' => 'required|integer|min:1|max:1000',
            'total_area' => 'required|numeric|min:0',
            'common_area_ratio' => 'required|numeric|min:0|max:100',
        ]);
        
        $this->siteService->updateSite($site, $validated);
        
        return redirect()
            ->route('sites.show', $site)
            ->with('success', 'Site bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified site.
     */
    public function destroy(Site $site)
    {
        $this->authorize('delete', $site);
        
        $this->siteService->deleteSite($site);
        
        return redirect()
            ->route('sites.index')
            ->with('success', 'Site başarıyla silindi.');
    }

    /**
     * Display apartments for the specified site.
     */
    public function apartments(Site $site)
    {
        $this->authorize('view', $site);
        
        $apartments = $site->apartments()
            ->with('residents')
            ->orderBy('block')
            ->orderBy('floor')
            ->orderBy('apartment_number')
            ->get();
        
        return view('sites.apartments', compact('site', 'apartments'));
    }

    /**
     * Store a new apartment for the site.
     */
    public function storeApartment(Request $request, Site $site)
    {
        $this->authorize('update', $site);
        
        $validated = $request->validate([
            'apartment_number' => 'required|string|max:20',
            'floor' => 'nullable|integer',
            'block' => 'nullable|string|max:10',
            'area' => 'required|numeric|min:0',
            'rooms' => 'nullable|string|max:10',
            'ownership_share' => 'required|numeric|min:0|max:100',
            'monthly_aidat' => 'required|numeric|min:0',
            'owner_name' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email|max:255',
        ]);
        
        $validated['site_id'] = $site->id;
        
        $apartment = Apartment::create($validated);
        
        return redirect()
            ->route('sites.apartments', $site)
            ->with('success', 'Daire başarıyla eklendi.');
    }
}