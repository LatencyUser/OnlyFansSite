<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const PENDING_STATUS = 'pending';
    const CANCELED_STATUS = 'canceled';
    const APPROVED_STATUS = 'approved';
    const DECLINED_STATUS = 'declined';
    const REFUNDED_STATUS = 'refunded';
    const INITIATED_STATUS = 'initiated';
    const PARTIALLY_PAID_STATUS = 'partially-paid';

    const TIP_TYPE = 'tip';
    const CHAT_TIP_TYPE = 'chat-tip';
    const POST_UNLOCK = 'post-unlock';
    const MESSAGE_UNLOCK = 'message-unlock';
    const DEPOSIT_TYPE = 'deposit';
    const WITHDRAWAL_TYPE = 'withdrawal';
    const ONE_MONTH_SUBSCRIPTION = 'one-month-subscription';
    const THREE_MONTHS_SUBSCRIPTION = 'three-months-subscription';
    const SIX_MONTHS_SUBSCRIPTION = 'six-months-subscription';
    const YEARLY_SUBSCRIPTION = 'yearly-subscription';
    const SUBSCRIPTION_RENEWAL = 'subscription-renewal';
    const STREAM_ACCESS = 'stream-access';

    const PAYPAL_PROVIDER = 'paypal';
    const STRIPE_PROVIDER = 'stripe';
    const MANUAL_PROVIDER = 'manual';
    const CREDIT_PROVIDER = 'credit';
    const COINBASE_PROVIDER = 'coinbase';
    const CCBILL_PROVIDER = 'ccbill';
    const NOWPAYMENTS_PROVIDER = 'nowpayments';
    const PAYSTACK_PROVIDER = 'paystack';

    const COINBASE_API_BASE_PATH = 'https://api.commerce.coinbase.com';
    const NOWPAYMENTS_API_BASE_PATH = 'https://api.nowpayments.io/v1/';

    const ALLOWED_PAYMENT_PROVIDERS = [
        self::NOWPAYMENTS_PROVIDER,
        self::COINBASE_PROVIDER,
        self::PAYPAL_PROVIDER,
        self::STRIPE_PROVIDER,
        self::CCBILL_PROVIDER,
        self::PAYSTACK_PROVIDER,
    ];

    const CCBILL_FLEX_FORM_BASE_PATH = 'https://api.ccbill.com/wap-frontflex/flexforms/';
    const CCBILL_CANCEL_SUBSCRIPTION_BASE_PATH = 'https://datalink.ccbill.com/utils/subscriptionManagement.cgi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_user_id', 'recipient_user_id', 'subscription_id', 'stripe_transaction_id', 'paypal_payer_id', 'post_id',
        'paypal_transaction_id', 'status', 'type', 'amount', 'payment_provider', 'paypal_transaction_token', 'currency', 'taxes',
        'coinbase_charge_id', 'coinbase_transaction_token', 'ccbill_payment_token', 'ccbill_transaction_id', 'nowpayments_payment_id',
        'nowpayments_order_id', 'stream_id', 'ccbill_subscription_id', 'user_message_id', 'paystack_transaction_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];

    /*
     * Relationships
     */

    public function receiver()
    {
        return $this->belongsTo('App\User', 'recipient_user_id');
    }

    public function sender()
    {
        return $this->belongsTo('App\User', 'sender_user_id');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Model\Subscription', 'subscription_id');
    }

    public function post()
    {
        return $this->belongsTo('App\Model\Post', 'post_id');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Model\Invoice', 'invoice_id');
    }

    public function stream()
    {
        return $this->belongsTo('App\Model\Stream', 'stream_id');
    }
}
