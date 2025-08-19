<?php

namespace App\Services;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SiteService
{
    /**
     * Create a new site.
     */
    public function createSite(array $data, User $creator): Site
    {
        return DB::transaction(function () use ($data, $creator) {
            $site = Site::create([
                ...$data,
                'manager_user_id' => $creator->id,
                'is_active' => true,
            ]);

            // Update creator's role if needed
            if ($creator->role === User::ROLE_SAKIN && !$creator->site_id) {
                $creator->update([
                    'role' => User::ROLE_SITE_YONETICISI,
                    'site_id' => $site->id,
                ]);
            }

            return $site;
        });
    }

    /**
     * Update an existing site.
     */
    public function updateSite(Site $site, array $data): Site
    {
        $site->update($data);
        
        return $site->fresh();
    }

    /**
     * Delete a site and handle related data.
     */
    public function deleteSite(Site $site): bool
    {
        return DB::transaction(function () use ($site) {
            // Check if site has active residents
            if ($site->residents()->count() > 0) {
                throw new \Exception('Site has active residents and cannot be deleted.');
            }

            // Check if site has pending payments
            if ($site->aidats()->pending()->count() > 0) {
                throw new \Exception('Site has pending payments and cannot be deleted.');
            }

            // Soft delete or handle related data as needed
            $site->delete();
            
            return true;
        });
    }

    /**
     * Get site statistics.
     */
    public function getSiteStatistics(Site $site): array
    {
        return [
            'total_apartments' => $site->apartments()->count(),
            'occupied_apartments' => $site->apartments()->occupied()->count(),
            'vacant_apartments' => $site->apartments()->vacant()->count(),
            'total_residents' => $site->residents()->count(),
            'monthly_income' => $site->total_monthly_aidat,
            'occupancy_rate' => $site->occupancy_rate,
            'pending_aidats' => $site->aidats()->pending()->count(),
            'overdue_aidats' => $site->aidats()->late()->count(),
            'total_debt' => $site->aidats()->pending()->sum('total_amount'),
            'monthly_expenses' => $site->expenses()->whereMonth('created_at', now()->month)->sum('amount'),
        ];
    }

    /**
     * Calculate total site expenses for a given period.
     */
    public function calculateSiteExpenses(Site $site, int $month, int $year): float
    {
        return $site->expenses()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');
    }

    /**
     * Distribute expenses to apartments based on ownership shares.
     */
    public function distributeExpenses(Site $site, float $totalExpenses): array
    {
        $apartments = $site->apartments;
        $distribution = [];

        foreach ($apartments as $apartment) {
            $share = $apartment->ownership_share / 100;
            $amount = $totalExpenses * $share;
            
            $distribution[$apartment->id] = [
                'apartment' => $apartment,
                'share' => $apartment->ownership_share,
                'amount' => round($amount, 2),
            ];
        }

        return $distribution;
    }

    /**
     * Get financial summary for a site.
     */
    public function getFinancialSummary(Site $site, int $year = null): array
    {
        $year = $year ?? now()->year;
        
        $totalIncome = $site->payments()
            ->completed()
            ->whereYear('paid_at', $year)
            ->sum('amount');
            
        $totalExpenses = $site->expenses()
            ->whereYear('created_at', $year)
            ->sum('amount');
            
        $pendingAidats = $site->aidats()
            ->pending()
            ->sum('total_amount');
            
        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalIncome - $totalExpenses,
            'pending_collections' => $pendingAidats,
            'year' => $year,
        ];
    }
}