<?php

namespace App\Console\Commands;

use App\Events\AttendanceRecorded;
use App\Jobs\SyncAttendanceJob;
use App\Services\FirebaseService;
use Illuminate\Console\Command;

class SyncAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance data from Firebase to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $firebaseService = app(FirebaseService::class);
        $firebaseService->syncAttendanceToDatabase();
        $this->info('Attendance data synchronized successfully.');
        event(new AttendanceRecorded());
    }
}
