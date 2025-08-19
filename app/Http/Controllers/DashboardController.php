<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Statistics based on user role
        $statistics = [];
        
        switch ($user->role) {
            case User::ROLE_ADMIN:
                $statistics = $this->getAdminStatistics();
                break;
            case User::ROLE_SITE_YONETICISI:
                $statistics = $this->getSiteManagerStatistics($user);
                break;
            case User::ROLE_SAKIN:
                $statistics = $this->getResidentStatistics($user);
                break;
            case User::ROLE_FIRMA_CALISANI:
                $statistics = $this->getCompanyEmployeeStatistics($user);
                break;
            default:
                $statistics = [];
        }
        
        return view('dashboard', compact('statistics'));
    }

    private function getAdminStatistics(): array
    {
        return [
            'total_sites' => Site::count(),
            'active_sites' => Site::active()->count(),
            'total_apartments' => Apartment::count(),
            'occupied_apartments' => Apartment::occupied()->count(),
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
        ];
    }

    private function getSiteManagerStatistics(User $user): array
    {
        $site = $user->site;
        
        if (!$site) {
            return [];
        }
        
        return [
            'site_name' => $site->name,
            'total_apartments' => $site->apartments()->count(),
            'occupied_apartments' => $site->apartments()->occupied()->count(),
            'total_residents' => $site->residents()->count(),
            'monthly_income' => $site->total_monthly_aidat,
            'occupancy_rate' => $site->occupancy_rate,
        ];
    }

    private function getResidentStatistics(User $user): array
    {
        $apartment = $user->apartment;
        
        if (!$apartment) {
            return [];
        }
        
        $pendingAidats = $user->aidats()->pending()->count();
        $totalDebt = $user->aidats()->pending()->sum('total_amount');
        
        return [
            'apartment' => $apartment->full_identifier,
            'site_name' => $apartment->site->name,
            'pending_aidats' => $pendingAidats,
            'total_debt' => $totalDebt,
            'monthly_aidat' => $apartment->monthly_aidat,
        ];
    }

    private function getCompanyEmployeeStatistics(User $user): array
    {
        // Company employees can manage multiple sites
        $managedSites = Site::where('manager_user_id', $user->id)->get();
        
        return [
            'managed_sites' => $managedSites->count(),
            'total_apartments' => $managedSites->sum('total_apartments'),
            'total_income' => $managedSites->sum('total_monthly_aidat'),
        ];
    }
}