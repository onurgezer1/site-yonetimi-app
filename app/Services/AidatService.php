<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Aidat;
use App\Models\Apartment;
use App\Jobs\CalculateMonthlyAidat;
use App\Jobs\SendAidatReminder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AidatService
{
    /**
     * Generate monthly aidat for all apartments in a site.
     */
    public function generateMonthlyAidat(Site $site, int $month, int $year): array
    {
        return DB::transaction(function () use ($site, $month, $year) {
            $results = [];
            
            // Check if aidats already exist for this period
            $existingCount = Aidat::where('site_id', $site->id)
                ->where('month', $month)
                ->where('year', $year)
                ->count();
                
            if ($existingCount > 0) {
                throw new \Exception("Bu dönem için aidat kayıtları zaten mevcut.");
            }
            
            $apartments = $site->apartments()->with('residents')->get();
            $dueDate = Carbon::create($year, $month, 15); // Aidat due date: 15th of the month
            
            foreach ($apartments as $apartment) {
                if ($apartment->residents->isEmpty()) {
                    continue; // Skip apartments without residents
                }
                
                $primaryResident = $apartment->residents->first();
                
                $aidat = Aidat::create([
                    'site_id' => $site->id,
                    'apartment_id' => $apartment->id,
                    'user_id' => $primaryResident->id,
                    'month' => $month,
                    'year' => $year,
                    'amount' => $apartment->monthly_aidat,
                    'late_fee' => 0,
                    'total_amount' => $apartment->monthly_aidat,
                    'due_date' => $dueDate,
                    'status' => Aidat::STATUS_PENDING,
                ]);
                
                $results[] = $aidat;
                
                // Queue reminder email/SMS
                SendAidatReminder::dispatch($aidat)->delay(now()->addDays(10));
            }
            
            return $results;
        });
    }

    /**
     * Generate aidat for a specific apartment.
     */
    public function generateApartmentAidat(
        Apartment $apartment, 
        int $month, 
        int $year, 
        float $customAmount = null
    ): Aidat {
        return DB::transaction(function () use ($apartment, $month, $year, $customAmount) {
            // Check if aidat already exists
            $existing = Aidat::where('apartment_id', $apartment->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();
                
            if ($existing) {
                throw new \Exception("Bu daire için bu dönemde aidat kaydı zaten mevcut.");
            }
            
            $primaryResident = $apartment->residents()->first();
            
            if (!$primaryResident) {
                throw new \Exception("Bu dairede kayıtlı sakin bulunmamaktadır.");
            }
            
            $amount = $customAmount ?? $apartment->monthly_aidat;
            $dueDate = Carbon::create($year, $month, 15);
            
            return Aidat::create([
                'site_id' => $apartment->site_id,
                'apartment_id' => $apartment->id,
                'user_id' => $primaryResident->id,
                'month' => $month,
                'year' => $year,
                'amount' => $amount,
                'late_fee' => 0,
                'total_amount' => $amount,
                'due_date' => $dueDate,
                'status' => Aidat::STATUS_PENDING,
            ]);
        });
    }

    /**
     * Update all late fees for overdue aidats.
     */
    public function updateLateFees(Site $site = null): int
    {
        $query = Aidat::pending()->where('due_date', '<', now());
        
        if ($site) {
            $query->where('site_id', $site->id);
        }
        
        $overdueAidats = $query->get();
        $updated = 0;
        
        foreach ($overdueAidats as $aidat) {
            $aidat->updateAmounts();
            $updated++;
        }
        
        return $updated;
    }

    /**
     * Calculate late fee for a specific aidat.
     */
    public function calculateLateFee(Aidat $aidat): float
    {
        return $aidat->calculateLateFee();
    }

    /**
     * Get aidat statistics for a site.
     */
    public function getAidatStatistics(Site $site, int $year = null): array
    {
        $year = $year ?? now()->year;
        
        $baseQuery = $site->aidats()->whereYear('created_at', $year);
        
        return [
            'total_aidats' => $baseQuery->count(),
            'paid_aidats' => $baseQuery->paid()->count(),
            'pending_aidats' => $baseQuery->pending()->count(),
            'late_aidats' => $baseQuery->late()->count(),
            'total_amount' => $baseQuery->sum('amount'),
            'paid_amount' => $baseQuery->paid()->sum('total_amount'),
            'pending_amount' => $baseQuery->pending()->sum('total_amount'),
            'late_fees' => $baseQuery->sum('late_fee'),
            'collection_rate' => $this->calculateCollectionRate($site, $year),
        ];
    }

    /**
     * Calculate collection rate for a site.
     */
    private function calculateCollectionRate(Site $site, int $year): float
    {
        $totalAmount = $site->aidats()
            ->whereYear('created_at', $year)
            ->sum('amount');
            
        $paidAmount = $site->payments()
            ->completed()
            ->whereYear('paid_at', $year)
            ->sum('amount');
            
        return $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
    }

    /**
     * Get monthly aidat summary for a site.
     */
    public function getMonthlyAidatSummary(Site $site, int $year): array
    {
        $summary = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthData = $site->aidats()
                ->forPeriod($month, $year)
                ->selectRaw('
                    COUNT(*) as total_count,
                    COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count,
                    COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as paid_amount,
                    SUM(late_fee) as total_late_fees
                ')
                ->first();
                
            $summary[$month] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month)->format('F'),
                'total_aidats' => $monthData->total_count ?? 0,
                'paid_aidats' => $monthData->paid_count ?? 0,
                'pending_aidats' => $monthData->pending_count ?? 0,
                'total_amount' => $monthData->total_amount ?? 0,
                'paid_amount' => $monthData->paid_amount ?? 0,
                'late_fees' => $monthData->total_late_fees ?? 0,
                'collection_rate' => $monthData->total_amount > 0 
                    ? ($monthData->paid_amount / $monthData->total_amount) * 100 
                    : 0,
            ];
        }
        
        return $summary;
    }

    /**
     * Send reminder notifications for overdue aidats.
     */
    public function sendOverdueReminders(Site $site = null): int
    {
        $query = Aidat::pending()
            ->where('due_date', '<', now()->subDays(7)); // 7 days overdue
            
        if ($site) {
            $query->where('site_id', $site->id);
        }
        
        $overdueAidats = $query->with(['user', 'apartment'])->get();
        
        foreach ($overdueAidats as $aidat) {
            SendAidatReminder::dispatch($aidat, true); // true for overdue reminder
        }
        
        return $overdueAidats->count();
    }
}