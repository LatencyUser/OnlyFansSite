<?php

namespace App\Observers;

use App\Model\ReferralCodeUsage;
use App\Model\Reward;
use App\Model\Transaction;
use App\Providers\PaymentsServiceProvider;
use App\Providers\UsersServiceProvider;
use App\User;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

class TransactionsObserver
{
    /**
     * Listen to the Transaction deleting event.
     *
     * @param  \App\Model\Transaction  $transaction
     * @return void
     */
    public function deleting(Transaction $transaction)
    {
        // removes invoice along with transaction
        if ($transaction->invoice()) {
            $transaction->invoice()->delete();
        }
    }

    /**
     * Listen to the Transaction created event
     * @param Transaction $transaction
     * @return void
     */
    public function created(Transaction $transaction) {
        if($transaction->status === Transaction::APPROVED_STATUS) {
            // first make sure there's a referral code usage entry for this user referral code
            $this->createRewardForTransaction($transaction);
        }
    }

    /**
     * Listen to the Transaction updated event
     * @param Transaction $transaction
     * @return void
     */
    public function updating(Transaction  $transaction) {
        if($transaction->getOriginal('status') !== $transaction->status && $transaction->status === Transaction::APPROVED_STATUS) {
            $this->createRewardForTransaction($transaction);
        }
    }

    private function createRewardForTransaction($transaction) {
        if(getSetting('referrals.enabled')) {
            try{
                if(floatval(getSetting('referrals.fee_percentage')) > 0) {
                    // make sure this transaction is not a top-up wallet payment
                    if($transaction->type === Transaction::DEPOSIT_TYPE || intval($transaction->recipient_user_id) === intval($transaction->sender_user_id)) {
                        return;
                    }

                    // make sure there's not already a reward generated for this transaction
                    $existingReward = Reward::where(['transaction_id' => $transaction->id])->first();
                    if(!$existingReward){
                        // check if there is a referral code usage for this user
                        $referralCodeUsage = ReferralCodeUsage::where(['used_by' => $transaction->recipient_user_id])->first();
                        if($referralCodeUsage) {
                            // find a user with this referral code
                            $referralCodeUser = User::where(['referral_code' => $referralCodeUsage->referral_code])->first();
                            if($referralCodeUser) {
                                if(getSetting('referrals.apply_for_months') && intval(getSetting('referrals.apply_for_months')) > 0) {
                                    $expiryDatetime = new \DateTime('-'.intval(getSetting('referrals.apply_for_months')).' months');

                                    // this referral is older enough so stop here and don't create anymore rewards for him
                                    if($expiryDatetime >= $referralCodeUsage->created_at) {
                                        return;
                                    }
                                }

                                $totalEarnedByUser = 0;
                                // make sure we don't send more money than the limit set by the admin
                                if(getSetting('referrals.fee_limit') && intval(getSetting('referrals.fee_limit')) > 0) {
                                    $totalEarnedByUser = UsersServiceProvider::getTotalAmountEarnedFromRewardsByUsers($referralCodeUser->id, $transaction->recipient_user_id);
                                    // reached maximum limit set by the admin
                                    if($totalEarnedByUser >= floatval(getSetting('referrals.fee_limit'))) {
                                        return;
                                    }
                                }

                                if($transaction->amount <= 0) {
                                    return;
                                }

                                // calculate transaction fee and add it to the total to make sure we don't send more than the threshold
                                $amountWithTaxesDeducted = $transaction->amount;
                                $taxes = PaymentsServiceProvider::calculateTaxesForTransaction($transaction);
                                if (isset($taxes['inclusiveTaxesAmount'])) {
                                    $amountWithTaxesDeducted = $amountWithTaxesDeducted - $taxes['inclusiveTaxesAmount'];
                                }

                                if (isset($taxes['exclusiveTaxesAmount'])) {
                                    $amountWithTaxesDeducted = $amountWithTaxesDeducted - $taxes['exclusiveTaxesAmount'];
                                }

                                $rewardFee = (floatval(getSetting('referrals.fee_percentage')) / 100) * $amountWithTaxesDeducted;
                                if($rewardFee + $totalEarnedByUser >= floatval(getSetting('referrals.fee_limit')) || $rewardFee === 0) {
                                    return;
                                }

                                Reward::create([
                                    'from_user_id' => $transaction->recipient_user_id,
                                    'to_user_id' => $referralCodeUser->id,
                                    'reward_type' => Reward::FEE_PERCENTAGE_REWARD_TYPE,
                                    'transaction_id' => $transaction->id,
                                    'referral_code_usage_id' => $referralCodeUsage->id,
                                    'amount' => $rewardFee,
                                ]);

                                // add money to user wallet
                                $recipientUser = User::where('id', $referralCodeUser->id)->first();
                                if($recipientUser) {
                                    $wallet = $recipientUser->wallet;
                                    $updateData = ['total' => $wallet->total + $rewardFee];
                                    $wallet->update($updateData);
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $exception){
                Log::log(LogLevel::ERROR, "Failed to generate reward: " . $exception->getMessage());
            }
        }
    }
}
