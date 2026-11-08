<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeAttendance;
use App\Models\Admin_user;
use App\Models\PayrollShiftManagement;
use Carbon\Carbon;


class HitApiAndSaveToDatabase extends Command
{
    protected $signature = 'api:hit-and-save';
    protected $description = 'Fetch today\'s attendance from API and sync with database';

    private $apiBaseUrl;
    private $apiKey;
    private $companyId;
    private $userIp;

    public function __construct()
    {
        parent::__construct();
        $this->apiBaseUrl = 'https://testzk.iglweb.com/api';
        $this->apiKey = config('services.zk_attendance.api_key');   // from .env (ZK_API_KEY)
        $this->companyId = 445001;
        $this->userIp = '27.147.177.102';
    }

    public function handle()
    {
        try {
            $today = Carbon::today()->format('Y-m-d');
            $url = "{$this->apiBaseUrl}/get-user-attendance-record";
    
            Log::info("Fetching today's ({$today}) attendance from: {$url}");
    
            $response = Http::withHeaders(['Authorization' => $this->apiKey])
                ->get($url, ['date' => $today]);
    
            if ($response->failed()) {
                Log::error("API Error: Status Code - " . $response->status());
                return;
            }
    
            $data = $response->json();
            
            // Add this to log the raw testzk data
            Log::info('Raw testzk data for today:', $data);
            
            $userRecords = $data['user_records'] ?? [];
    
            foreach ($userRecords as $record) {
                $this->processUserRecord($record);
            }
    
            Log::info("Today's attendance sync completed");
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
        }
    }

    private function processUserRecord($record)
    {
        $adminUser = Admin_user::where('au_user_id', $record['user_id'])->first();
        $userId = $adminUser?->au_id ?? $record['user_id'];

        if (isset($record['checkin_timestamp'])) {
            $this->syncCheckIn($record, $userId);
        }

        if (isset($record['checkout_timestamp'])) {
            $this->syncCheckOut($record, $userId);
        }
    }

  private function syncCheckIn($record, $userId)
{
    try {
        // Combine date and time to match DB format
        $fullCheckInString = $record['work_date'] . ' ' . $record['checkin_time'];
        $checkInTime = Carbon::parse($fullCheckInString);

        // Log::info("Processing check-in for user {$userId}", [
        //     'raw_api_timestamp' => $fullCheckInString,
        //     'parsed_api_time' => $checkInTime->toDateTimeString()
        // ]);

        $existing = EmployeeAttendance::where('company_id', $this->companyId)
            ->where('user_id', $userId)
            ->whereDate('check_in', $checkInTime->toDateString())
            ->first();

        if ($existing) {
            $existingCheckIn = Carbon::parse($existing->check_in);

            $shouldUpdate = false;

            // Compare full datetime
            if ($checkInTime->lt($existingCheckIn)) {
                $existing->check_in = $checkInTime;
                $shouldUpdate = true;
            }

            if ($existing->status !== 0) {
                $existing->status = 0;
                $shouldUpdate = true;
            }

            if ($existing->in_click != 1) {
                $existing->in_click = 1;
                $shouldUpdate = true;
            }

            if ($existing->user_ip !== $this->userIp) {
                $existing->user_ip = $this->userIp;
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                $existing->save();

                // Log::info("Updated existing check-in record", [
                //     'user_id' => $userId,
                //     'updated_fields' => $existing->getChanges()
                // ]);
            } else {
                // Log::info("No update required for check-in", [
                //     'user_id' => $userId,
                //     'check_in' => $existingCheckIn->toDateTimeString()
                // ]);
            }
        } else {
            $newRecord = EmployeeAttendance::create([
                'company_id' => $this->companyId,
                'user_id' => $userId,
                'user_ip' => $this->userIp,
                'check_in' => $checkInTime,
                'status' => 0,
                'in_click' => 1
            ]);

            if ($newRecord->exists) {
                // Log::info("Created new check-in record", [
                //     'user_id' => $userId,
                //     'check_in' => $checkInTime->toDateTimeString()
                // ]);
            }
        }
    } catch (\Exception $e) {
        // Log::error("Check-in sync failed", [
        //     'user_id' => $userId,
        //     'error' => $e->getMessage(),
        //     'trace' => $e->getTraceAsString()
        // ]);
    }
}

private function syncCheckOut($record, $userId)
{
    try {
        $checkOutTime = Carbon::parse($record['checkout_timestamp']); // Already in local time
        $checkInDate = Carbon::parse($record['checkin_timestamp'])->toDateString();

        // Log::info("Processing check-out for user {$userId}", [
        //     'checkout_time' => $checkOutTime->toDateTimeString()
        // ]);

        $attendance = EmployeeAttendance::where('company_id', $this->companyId)
            ->where('user_id', $userId)
            ->whereDate('check_in', $checkInDate)
            ->first();

        if ($attendance) {
            $existingCheckOut = $attendance->check_out ? Carbon::parse($attendance->check_out) : null;
            
            // Log::debug("Existing check-out comparison", [
            //     'existing_checkout' => $existingCheckOut ? $existingCheckOut->toDateTimeString() : null,
            //     'new_checkout' => $checkOutTime->toDateTimeString(),
            //     'new_is_later' => $existingCheckOut ? $checkOutTime->gt($existingCheckOut) : true
            // ]);

            // Update if no existing checkout or new checkout is later
            if (!$existingCheckOut || $checkOutTime->gt($existingCheckOut)) {
                $updateData = [
                    'check_out' => $checkOutTime,
                    'total_hour' => $this->calculateTotalHours($attendance->check_in, $checkOutTime),
                    'out_click' => 2,
                    'user_ip_checkout' => $this->userIp
                ];
                
                if ($attendance->update($updateData)) {
                    $this->calculateWorkStatus($attendance);

                    // Log::info("Successfully updated check-out", [
                    //     'user_id' => $userId,
                    //     'updated_check_out' => $checkOutTime->toDateTimeString(),
                    //     'total_hours' => $updateData['total_hour']
                    // ]);
                } else {
                    // Log::error("Failed to update check-out", [
                    //     'user_id' => $userId,
                    //     'errors' => $attendance->getErrors()
                    // ]);
                }
            }
        } else {
            // Log::warning("No matching check-in found for check-out", [
            //     'user_id' => $userId,
            //     'checkout_time' => $checkOutTime->toDateTimeString()
            // ]);
        }
    } catch (\Exception $e) {
        // Log::error("Check-out sync failed", [
        //     'user_id' => $userId,
        //     'error' => $e->getMessage(),
        //     'trace' => $e->getTraceAsString()
        // ]);
    }
}

    private function calculateTotalHours($checkIn, $checkOut)
    {
        return Carbon::parse($checkIn)->diffInHours(Carbon::parse($checkOut), true);
    }

    private function calculateWorkStatus($attendance)
    {
        $shift = PayrollShiftManagement::where('company_id', $this->companyId)->first();
        
        if (!$shift || !str_contains($shift->working_hour, ',')) {
            Log::error('Invalid shift configuration');
            return;
        }

        [$shiftStart, $shiftEnd] = explode(',', $shift->working_hour);
        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($attendance->check_out);

        $shiftStartTime = Carbon::parse($checkIn->format('Y-m-d') . ' ' . $shiftStart);
        $shiftEndTime = Carbon::parse($checkIn->format('Y-m-d') . ' ' . $shiftEnd);

        $effectiveStart = $checkIn->gt($shiftStartTime) ? $checkIn : $shiftStartTime;
        $effectiveEnd = $checkOut->lt($shiftEndTime) ? $checkOut : $shiftEndTime;

        $worked = $checkOut->diffInSeconds($checkIn);
        $required = $effectiveEnd->diffInSeconds($effectiveStart);

        if ($worked < $required) {
            $diff = $required - $worked;
            $attendance->update([
                'incomplete_hour' => intdiv($diff, 3600),
                'incomplete_minute' => intdiv($diff % 3600, 60),
                'incomplete_sec' => $diff % 60,
                'status' => 'Incomplete'
            ]);
        } else {
            $diff = $worked - $required;
            $attendance->update([
                'over_hour' => intdiv($diff, 3600),
                'over_minute' => intdiv($diff % 3600, 60),
                'over_sec' => $diff % 60,
                'status' => 'Overtime'
            ]);
        }
    }
}