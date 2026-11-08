<?php

namespace App\Console\Commands;

use App\Models\Admin_user;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class cron_user_inactive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inactive:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $start_date = Carbon::now()->subDays(5);
        $end_date = Carbon::now();

        // get active user
        $usersAttendance = DB::table('employee_attendances')
            ->whereBetween('check_in',[$start_date,$end_date])
            ->groupBy('user_id')
            ->pluck('user_id');
        //inactive
        // user update wherenotin [

        $inactive_user = Admin_user::whereNotIn('au_id',$usersAttendance)->whereNotIn('au_id',[1,2,64,59,63])->get();
        foreach ($inactive_user as $user) {
               if ($user->au_department !== '2'){
                $user->au_status = 0;
                $user-> update(); 
            }
        }
    }
}
