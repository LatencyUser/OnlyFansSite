<?php

namespace App\Providers;

use App\Model\Attachment;
use App\Model\PaymentRequest;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class PaymentRequestServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        UserVerify::observe(UserVerifyObserver::class);
        Schema::defaultStringLength(191);
    }

    /**
     * Creates a payment request for admins by a transaction
     * @param $transaction
     */
    public static function createDepositPaymentRequestByTransaction($transaction, $files, $description){
        $paymentRequest = PaymentRequest::create([
            'type' => PaymentRequest::DEPOSIT_TYPE,
            'user_id' => $transaction['recipient_user_id'],
            'transaction_id' => $transaction['id'],
            'amount' => $transaction['amount'],
            'message' => $description
        ]);

        if($paymentRequest){
            if($files && strlen($files) > 0) {
                $filesArray = explode(',', $files);
                if(count($filesArray)){
                    foreach ($filesArray as $attachmentId){
                        $attachment = Attachment::query()->where('id', $attachmentId)->first();
                        if($attachment!=null){
                            $attachment->update(['payment_request_id' => $paymentRequest['id']]);
                        }
                    }
                }
            }

            // Sending out admin email
            $adminEmails = User::where('role_id', 1)->select(['email', 'name'])->get();
            foreach ($adminEmails as $user) {
                EmailsServiceProvider::sendGenericEmail(
                    [
                        'email' => $user->email,
                        'subject' => __('Action required | New payment request'),
                        'title' => __('Hello, :name,', ['name' => $user->name]),
                        'content' => __('There is a new payment request on :siteName that requires your attention.', ['siteName' => getSetting('site.name')]),
                        'button' => [
                            'text' => __('Go to admin'),
                            'url' => route('voyager.dashboard').'/payment-requests',
                        ],
                    ]
                );
            }
        }
    }
}
