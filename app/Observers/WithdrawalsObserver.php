<?php

namespace App\Observers;

use App\Model\Wallet;
use App\Model\Withdrawal;
use App\Providers\EmailsServiceProvider;
use App\Providers\NotificationServiceProvider;
use App\Providers\PaymentsServiceProvider;
use App\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class WithdrawalsObserver
{
    /**
     * Listen to the Withdrawal updating event.
     *
     * @param  \App\Model\Withdrawal  $withdrawal
     * @return void
     */
    public function saving(Withdrawal $withdrawal)
    {
        if ($withdrawal->getOriginal('status') == 'requested' && $withdrawal->status != 'requested') {
            if(!$withdrawal->processed) {
                if ($withdrawal->status == 'rejected') {
                    self::handleWithdrawalRejection($withdrawal);
                } elseif ($withdrawal->status = 'approved') {
                    PaymentsServiceProvider::createTransactionForWithdrawal($withdrawal);

                    $emailSubject = __('Your withdrawal request has been approved.');
                    $button = [
                        'text' => __('My payments'),
                        'url' => route('my.settings', ['type'=>'payments']),
                    ];

                    self::processWithdrawalNotifications($withdrawal, $emailSubject, $button);

                    // mark withdrawal as processed
                    $withdrawal->processed = true;
                }
            }
        }
    }

    /**
     * Handles the Withdrawal deletion event.
     *
     * @param Withdrawal $withdrawal
     * @return void
     */
    public function deleted(Withdrawal $withdrawal)
    {
        if(!$withdrawal->processed){
            self::handleWithdrawalRejection($withdrawal);
        }
    }

    /**
     * Returns money to the user and send notifications for a rejected/deleted withdrawal
     * @param $withdrawal
     */
    private function handleWithdrawalRejection($withdrawal){
        self::creditUserForRejetectedWithdrawal($withdrawal);
        $emailSubject = __('Your withdrawal request has been denied.');
        $button = [
            'text' => __('Try again'),
            'url' => route('my.settings', ['type'=>'wallet']),
        ];

        self::processWithdrawalNotifications($withdrawal, $emailSubject, $button);

        // mark withdrawal as processed
        $withdrawal->processed = true;
    }

    /**
     * Creates email / user notifications
     * @param $withdrawal
     * @param $emailSubject
     * @param $button
     */
    private function processWithdrawalNotifications($withdrawal, $emailSubject, $button){
        // Sending out the user notification
        $user = User::find($withdrawal->user_id);
        App::setLocale($user->settings['locale']);
        EmailsServiceProvider::sendGenericEmail(
            [
                'email' => $user->email,
                'subject' => $emailSubject,
                'title' => __('Hello, :name,', ['name'=>$user->name]),
                'content' => __('Email withdrawal processed', [
                        'siteName' => getSetting('site.name'),
                        'status' => __($withdrawal->status),
                    ]).($withdrawal->status == 'approved' ? ' $'.$withdrawal->amount.' '.__('has been sent to your account.') : ''),
                'button' => $button,
            ]
        );
        NotificationServiceProvider::createApprovedOrRejectedWithdrawalNotification($withdrawal);
    }


    /**
     * Restoring the money to the user
     * @param $withdrawal
     */
    public static function creditUserForRejetectedWithdrawal($withdrawal) {
        // Restoring the money to the user
        $userId = $withdrawal->user_id;
        $wallet = Wallet::where('user_id',$userId)->first();
        $wallet->update(['total' => $wallet->total + floatval($withdrawal->amount)]);
    }
}
