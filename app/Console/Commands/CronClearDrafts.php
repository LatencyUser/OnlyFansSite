<?php

namespace App\Console\Commands;

use App\Model\Attachment;
use App\Providers\AttachmentServiceProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CronClearDrafts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:clear_draft_files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears un-attached attachments';

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
     * Clears old zombie attachments (never uploaded drafts).
     *
     * @return mixed
     */
    public function handle()
    {

        $attachments = Attachment::where(['post_id' => null, 'message_id' => null])
            ->where('created_at', '<=', Carbon::now()->subDay()->toDateTimeString())
            ->get();

        foreach($attachments as $attachment){
            AttachmentServiceProvider::removeAttachment($attachment);
            Attachment::find($attachment->id)->delete();
        }

        echo '[*]['.date('H:i:s')."] Zombie draft assets deleted. Total files: ".count($attachments).".\r\n";
        Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Zombie draft assets deleted. Total files: ".count($attachments).".\r\n");
        return 0;
    }
}
