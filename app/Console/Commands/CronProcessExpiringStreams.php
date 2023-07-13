<?php

namespace App\Console\Commands;

use App\Model\Stream;
use App\Providers\NotificationServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CronProcessExpiringStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:process_expiring_streams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emails users about their streams about to expire in the next 30 minutes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Process about to expire streams (30 minutes before stream has reached maximum limit time)
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Processing about to expire streams.\r\n");

        $maxStreamDuration = intval(getSetting('streams.max_live_duration'));
        $maxStreamDuration = $maxStreamDuration >= 1 ? $maxStreamDuration : 1;
        $nowDate = new \DateTime();
        $formattedDate = $nowDate->format('Y-m-d H:i:s');
        $pendingExpiringStreams = Stream::query()->with('user')
            ->whereRaw("DATE_SUB(DATE_ADD(created_at, INTERVAL {$maxStreamDuration} HOUR), INTERVAL 30 MINUTE) <= '{$formattedDate}'")
            ->where('status', '=', Stream::IN_PROGRESS_STATUS)
            ->where('sent_expiring_reminder', '=', 0)
            ->get();

        if (count($pendingExpiringStreams) < 1) {
            Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] No pending streams about to expire in the next 30 minutes.\r\n");

            return;
        }

        foreach ($pendingExpiringStreams as $expiringStream) {
            try{
                NotificationServiceProvider::createExpiringStreamNotifications($expiringStream);
                $expiringStream->sent_expiring_reminder = 1;
                $expiringStream->update();

                Log::channel('cronjobs')->info('[*]['.date('H:i:s').'] Successfully sent notifications for expiring stream:'.$expiringStream->id.".\r\n");
            } catch (\Exception $exception){
                Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Error processing expiring stream ".$expiringStream->id." error: ".$exception->getMessage());
            }
        }

        Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Finished processing expiring streams.\r\n");
        return 0;
    }
}
