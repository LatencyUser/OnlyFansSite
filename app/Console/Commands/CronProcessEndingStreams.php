<?php

namespace App\Console\Commands;

use App\Model\Stream;
use App\Providers\StreamsServiceProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CronProcessEndingStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:end_streams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically end in progress streams that reached the maximum time duration';

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
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Start processing ending streams.\r\n");

        $maxStreamDuration = intval(getSetting('streams.max_live_duration'));
        $maxStreamDuration = $maxStreamDuration >= 1 ? $maxStreamDuration : 1;
        $nowDate = new \DateTime();
        $formattedDate = $nowDate->format('Y-m-d H:i:s');

        $endingStreams = Stream::query()->with('user')
            ->whereRaw("DATE_ADD(created_at, INTERVAL {$maxStreamDuration} HOUR) <= '{$formattedDate}'")
            ->where('status', '=', Stream::IN_PROGRESS_STATUS)
            ->get();

        if (count($endingStreams) < 1) {
            Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] No pending streams to end.\r\n");

            return;
        }

        foreach ($endingStreams as $endingStream) {
            try{

                if($endingStream->settings['dvr']){
                    $dvrDetails = StreamsServiceProvider::getPushrStreamingDvr($endingStream->pushr_id);
                    if($dvrDetails){
                        $endingStream->vod_link = $dvrDetails[$endingStream->pushr_id][0]['dvr_url'];
                    }
                }
                StreamsServiceProvider::destroyPushrStream($endingStream->pushr_id);
                $endingStream->ended_at = Carbon::now();
                $endingStream->status = Stream::ENDED_STATUS;
                $endingStream->save();

                Log::channel('cronjobs')->info('[*]['.date('H:i:s').'] Successfully ended stream: '.$endingStream->id.".\r\n");
            } catch (\Exception $exception){
                Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Error ending stream ".$endingStream->id." error: ".$exception->getMessage());
            }
        }

        Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Finished processing ending streams.\r\n");
        return 0;
    }
}
