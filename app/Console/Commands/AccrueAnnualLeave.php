<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AccrueAnnualLeave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:accrue-annual-leave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increment annual leave (sisa_cuti) for all active employees';

    public function handle()
    {
        $this->info('Starting annual leave accrual process...');

        $activeUsers = \App\Models\User::where('status_aktif', '=', 1)->get();
        $count = 0;

        foreach ($activeUsers as $user) {

            $user->increment('sisa_cuti', 12);
            $count++;
        }

        $this->success("Successfully accrued leave for {$count} active employees.");
    }
}
