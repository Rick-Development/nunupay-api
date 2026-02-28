<?php

namespace App\Observers;

use App\Models\CountryCurrency;
use App\Models\NotificationPermission;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\UserWallet;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {

        $currencyCodes = CountryCurrency::where('default', 1)->distinct()->pluck('code');
        $first = true;
        foreach ($currencyCodes as $code) {
            $wallet = UserWallet::create([
                'user_id' => $user->id,
                'currency_code' => $code,
                'balance' => 0,
                'status' => 1,
                'default' => $first ? 1 : 0,
            ]);
            $first = false;
        }


        $template_keys = NotificationTemplate::where('notify_for', 0)
            ->where(function ($query) {
                $query->whereNotNull('email')
                    ->orWhereNotNull('sms')
                    ->orWhereNotNull('push')
                    ->orWhereNotNull('in_app');
            })
            ->pluck('template_key');
        $notifyFor = new NotificationPermission();
        $notifyFor->notifyable_id = $user->id;
        $notifyFor->notifyable_type = User::class;
        $notifyFor->template_email_key = $template_keys;
        $notifyFor->template_sms_key = $template_keys;
        $notifyFor->template_push_key = $template_keys;
        $notifyFor->template_in_app_key = $template_keys;
        $notifyFor->save();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
