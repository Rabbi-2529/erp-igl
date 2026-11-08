<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeBloodDonate;
use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckBloodDonorAvailability extends Command
{
    // The name and signature of the console command.
    protected $signature = 'donors:check-availability';

    // The console command description.
    protected $description = 'Check the availability of blood donors based on their last donation date';

    // Execute the console command.
    public function handle()
    {
        // Get all employees who have donated blood
        $donors = EmployeeBloodDonate::distinct('employee_id')->pluck('employee_id');

        // Log the start of the operation
        Log::info('Checking blood donor availability...');

        foreach ($donors as $employeeId) {
            // Get the last donation date for the current employee
            $lastDonation = EmployeeBloodDonate::where('employee_id', $employeeId)
                                                ->orderBy('donate_date', 'desc')
                                                ->first();

            if ($lastDonation) {
                // Get the last donation date
                $lastDonationDate = Carbon::parse($lastDonation->donate_date);
                
                // Check if the last donation was more than 3 months ago
                $threeMonthsAgo = Carbon::now()->subMonths(3);

                // Determine availability status
                $status = $lastDonationDate->lt($threeMonthsAgo) ? 'Available' : 'Not Available';

                // Update the donor status
                $this->updateDonorStatus($employeeId, $status);

                // Log the donor status change
                Log::info("Donor ID {$employeeId} status updated to {$status}.");
            }
        }

        // Log the completion of the operation
        Log::info('Finished checking blood donor availability.');
    }

    /**
     * Update the donor status.
     *
     * @param  int  $employeeId
     * @param  string  $status
     * @return void
     */
    private function updateDonorStatus($employeeId, $status)
    {
        // Update the latest donation record's status
        EmployeeBloodDonate::where('employee_id', $employeeId)
            ->orderBy('donate_date', 'desc')
            ->first()
            ->update(['status' => $status]);
    }
}
