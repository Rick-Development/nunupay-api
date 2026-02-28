<?php

namespace App\Jobs;

use App\Models\SupportTicket;
use App\Traits\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UserAllRecordDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Upload;

    /**
     * Create a new job instance.
     */
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table('deposits')->where('user_id', $this->user->id)->delete();
        DB::table('transactions')->where('user_id', $this->user->id)->delete();
        DB::table('user_kycs')->where('user_id', $this->user->id)->delete();
        DB::table('user_logins')->where('user_id', $this->user->id)->delete();
        DB::table('user_wallets')->where('user_id', $this->user->id)->delete();
        DB::table('money_transfers')->where('sender_id', $this->user->id)->delete();

        SupportTicket::where('user_id', $this->user->id)->get()->map(function ($item) {
            $item->messages()->get()->map(function ($message) {
                if (count($message->attachments) > 0) {
                    foreach ($message->attachments as $img) {
                        $this->fileDelete($img->driver, $img->file);
                        $img->delete();
                    }
                }
            });
            $item->messages()->delete();
            $item->delete();
        });


        DB::table('in_app_notifications')->where('in_app_notificationable_id', $this->user->id)->where('in_app_notificationable_type','App\Models\User')->delete();
        DB::table('notification_permissions')->where('notifyable_id', $this->user->id)->where('notifyable_type','App\Models\User')->delete();
    }
}
