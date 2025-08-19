<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\AidatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class CalculateMonthlyAidat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Site $site,
        public int $month,
        public int $year
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AidatService $aidatService): void
    {
        try {
            $aidats = $aidatService->generateMonthlyAidat($this->site, $this->month, $this->year);
            
            \Log::info('Monthly aidat calculation completed', [
                'site_id' => $this->site->id,
                'site_name' => $this->site->name,
                'month' => $this->month,
                'year' => $this->year,
                'aidats_created' => count($aidats),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Monthly aidat calculation failed', [
                'site_id' => $this->site->id,
                'month' => $this->month,
                'year' => $this->year,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Monthly aidat calculation job failed', [
            'site_id' => $this->site->id,
            'month' => $this->month,
            'year' => $this->year,
            'exception' => $exception->getMessage(),
        ]);
    }
}