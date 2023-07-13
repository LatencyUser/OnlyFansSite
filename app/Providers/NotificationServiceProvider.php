<?php

namespace App\Providers;

use App\Model\Notification;
use App\Model\Post;
use App\Model\PostComment;
use App\Model\Stream;
use App\Model\Transaction;
use App\Model\UserListMember;
use App\Model\UserMessage;
use App\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\ServiceProvider;
use Pusher\Pusher;
use Ramsey\Uuid\Uuid;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Creates a notification payload and broadcasts it.
     *
     * @param $type
     * @param null $toUser
     * @param null $post
     * @param null $postComment
     * @param null $subscription
     * @param null $transaction
     * @param null $reaction
     * @param null $withdrawal
     * @param null $userMessage
     * @param null $stream
     * @return |null
     */
    public static function createAndPublishNotification(
        $type,
        $toUser = null,
        $post = null,
        $postComment = null,
        $subscription = null,
        $transaction = null,
        $reaction = null,
        $withdrawal = null,
        $userMessage = null,
        $stream = null
    ) {
        try {
            // generate unique id for notification
            do {
                $id = Uuid::uuid4()->getHex();
            } while (Notification::query()->where('id', $id)->first() != null);

            $notificationData = [];
            $notificationData['id'] = $id;
            $notificationData['from_user_id'] = Auth::id();
            $notificationData['type'] = $type;
            $notificationData['to_user_id'] = null;

            if ($post != null && isset($post->id) && isset($post->user_id)) {
                $notificationData['post_id'] = $post->id;
                $notificationData['message'] = __('post notification');
                $notificationData['to_user_id'] = $post->user_id;
            }

            // New post comment
            if ($postComment != null && isset($postComment->id) && isset($postComment->message) && isset($postComment->post_id)) {
                $post = Post::query()->where('id', $postComment->post_id)->first();
                App::setLocale($post->user->settings['locale']); // Setting the locale of the message receiver
                // Building up the notification message to be broadcasted & db saved
                if ($post != null) {
                    $fromUser = User::query()->where('id', $postComment->user_id)->first();
                    if ($fromUser != null) {
                        $notificationData['message'] = __(':name added a new comment on your post', ['name'=>$fromUser->name]);
                    }
                    $notificationData['post_comment_id'] = $postComment->id;
                    $notificationData['to_user_id'] = $post->user_id;
                }
                // Sending the user email notification
                $user = User::where('id', $post->user_id)->select(['email', 'name', 'settings'])->first();
                if (isset($user->settings['notification_email_new_comment']) && $user->settings['notification_email_new_comment'] == 'true') {
                    EmailsServiceProvider::sendGenericEmail(
                        [
                            'email' => $user->email,
                            'subject' => __('New comment received'),
                            'title' => __('Hello, :name,', ['name'=>$user->name]),
                            'content' =>  __("You've received a new comment on one of your posts at :siteName.", ['siteName'=>getSetting('site.name')]),
                            'button' => [
                                'text' => __('Your notifications'),
                                'url' => route('my.notifications'),
                            ],
                        ]
                    );
                }
            }

            // New subscription
            if ($subscription != null && isset($subscription->id) && isset($subscription->sender_user_id)
                && isset($subscription->recipient_user_id)) {
                $notificationData['subscription_id'] = $subscription->id;
                $notificationData['to_user_id'] = $subscription->recipient_user_id;
                $notificationData['from_user_id'] = $subscription->sender_user_id;
                // Setting the locale of the message receiver
                $user = User::where('id', $subscription->recipient_user_id)->select(['email', 'name', 'settings'])->first();
                App::setLocale($user->settings['locale']);
                // Building up the notification message to be broadcasted & db saved
                $subscriber = User::query()->where('id', $subscription->sender_user_id)->first();
                if ($subscriber != null) {
                    $notificationData['message'] = __('New subscription from :name', ['name'=>$subscriber->name]);
                } else {
                    $notificationData['message'] = __('A new user subscribed to your profile');
                }
                ListsHelperServiceProvider::managePredefinedUserMemberList($subscription->sender_user_id, $subscription->recipient_user_id, 'follow'); // TODO: Inspect
                // Sending the user email notification
                if (isset($user->settings['notification_email_new_sub']) && $user->settings['notification_email_new_sub'] == 'true') {
                    EmailsServiceProvider::sendGenericEmail(
                        [
                            'email' => $user->email,
                            'subject' => __('You got a new subscriber!'),
                            'title' => __('Hello, :name,', ['name'=>$user->name]),
                            'content' => __('You got a new subscriber! You can see more details over your subscriptions tab.'),
                            'button' => [
                                'text' => __('Manage your subs'),
                                'url' => route('my.settings', ['type' => 'subscriptions']),
                            ],
                        ]
                    );
                }
            }

            // New tip
            if ($transaction != null && isset($transaction->id) && isset($transaction->sender_user_id)
                && isset($transaction->amount) && isset($transaction->currency) && isset($transaction->recipient_user_id)) {
                $notificationData['transaction_id'] = $transaction->id;
                $notificationData['to_user_id'] = $transaction->recipient_user_id;
                // Setting the locale of the message receiver
                $user = User::where('id', $transaction->recipient_user_id)->select(['email', 'username', 'name', 'settings'])->first();
                App::setLocale($user->settings['locale']);
                // Building up the notification message to be broadcasted & db saved
                $sender = User::query()->where('id', $transaction->sender_user_id)->first();
                if ($sender != null) {
                    $amount = PaymentsServiceProvider::getTransactionAmountWithTaxesDeducted($transaction);
                    $notificationData['message'] = $sender->name.' '.__('sent you a tip of').' '.$amount.$transaction->currency.'.';
                }
                // Sending the user email notification
                if (isset($user->settings['notification_email_new_tip']) && $user->settings['notification_email_new_tip'] == 'true') {
                    EmailsServiceProvider::sendGenericEmail(
                        [
                            'email' => $user->email,
                            'subject' => __('You got a new tip!'),
                            'title' => __('Hello, :name,', ['name'=>$user->name]),
                            'content' => $notificationData['message'],
                            'button' => [
                                'text' => __('Your notifications'),
                                'url' => route('my.notifications', ['type'=>'subscriptions']),
                            ],
                        ]
                    );
                }
            }

            // New post / comment reaction
            if ($reaction != null && isset($reaction->id) && isset($reaction->user_id)) {
                $user = User::query()->where('id', $reaction->user_id)->first();
                // Post reaction
                if ($user != null) {
                    if (isset($reaction->post_id)) {
                        $post = Post::query()->where('id', $reaction->post_id)->first();
                        if ($post != null) {
                            // Setting the locale of the message receiver
                            $toUser = User::where('id', $post->user_id)->select(['email', 'username', 'name', 'settings'])->first();
                            App::setLocale($user->settings['locale']);
                            // Building up the notification message to be broadcasted & db saved
                            $notificationData['message'] = __(':name liked your post', ['name'=>$user->name]);
                            $notificationData['post_id'] = $post->id;
                            $notificationData['to_user_id'] = $post->user_id;
                        }
                    }
                    // Post comment reaction
                    if (isset($reaction->post_comment_id)) {
                        $postComment = PostComment::query()->where('id', $reaction->post_comment_id)->first();
                        if ($postComment != null) {
                            // Setting the locale of the message receiver
                            $toUser = User::where('id', $postComment->user_id)->select(['email', 'username', 'name', 'settings'])->first();
                            App::setLocale($user->settings['locale']);
                            // Building up the notification message to be broadcasted & db saved
                            $notificationData['message'] = __(':name liked your comment', ['name'=>$user->name]);
                            $notificationData['post_comment_id'] = $postComment->id;
                            $notificationData['to_user_id'] = $postComment->user_id;
                        }
                    }
                }
                $notificationData['reaction_id'] = $reaction->id;
            }

            // Withdrawal request
            if ($withdrawal != null && isset($withdrawal->id) && isset($withdrawal->user_id) && isset($withdrawal->amount)
                && isset($withdrawal->status)) {
                // Setting the locale of the message receiver
                $toUser = User::where('id', $withdrawal->user_id)->select(['email', 'username', 'name', 'settings'])->first();
                App::setLocale($toUser->settings['locale']);
                // Building up the notification message to be broadcasted & db saved
                $notificationData['withdrawal_id'] = $withdrawal->id;
                $notificationData['to_user_id'] = $withdrawal->user_id;
                $notificationData['message'] = __('Withdrawal processed', [
                    'currencySymbol' => SettingsServiceProvider::getWebsiteCurrencySymbol(),
                    'amount' => $withdrawal->amount,
                    'status' =>  $withdrawal->status,
                ]);
            }

            // New user message
            if ($userMessage != null && isset($userMessage->id) && isset($userMessage->sender_id) && isset($userMessage->receiver_id)
                && isset($userMessage->message)) {
                $notificationData['user_message_id'] = $userMessage->id;
                $notificationData['to_user_id'] = $userMessage->receiver_id;
                $notificationData['message'] = $userMessage->message;
            }

            // Expiring live streaming message and email notification
            if($stream && $type === Notification::EXPIRING_STREAM) {
                // Setting the locale of the message receiver
                App::setLocale($stream->user->settings['locale']);
                $message = __('Your live stream is about to end in 30 minutes. You can start another one afterwards.');
                $notificationData['message'] = $message;
                $notificationData['to_user_id'] = $stream->user->id;
                // send email notification
                EmailsServiceProvider::sendGenericEmail(
                    [
                        'email' => $stream->user->email,
                        'subject' => __('Your live stream is about to end'),
                        'title' => __('Hello, :name,', ['name'=>$stream->user->name]),
                        'content' =>  $message,
                        'button' => [
                            'text' => __('Watch streaming'),
                            'url' => Redirect::route('public.stream.get', ['streamID' => $stream->id, 'slug' => $stream->slug])->getTargetUrl(),
                        ],
                    ]
                );
            }

            if ($toUser == null && $notificationData['to_user_id'] == null) {
                return null;
            }

            if ($toUser != null && isset($toUser->id) && $notificationData['to_user_id'] == null) {
                $notificationData['to_user_id'] = $toUser->id;
            }

            $toUser = User::query()->where('id', $notificationData['to_user_id'])->first();
            if ($toUser != null) {
                $modelData = $notificationData;
                unset($modelData['message']);
                $notification = Notification::create($modelData);
                $notification->setAttribute('message',$notificationData['message']);
                self::publishNotification($notification, $toUser);
            }
        } catch (\Exception $exception) {
            Log::error('Failed sending notification: '.$exception->getMessage());
        }
    }

    /**
     * Dispatches the notification to puser.
     *
     * @param $notification
     * @param $toUser
     */
    private static function publishNotification($notification, $toUser)
    {
        try {
            $options = [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS' => true,
            ];
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                $options
            );
            $data['message'] = $notification->message;
            $data['type'] = $notification->type;
            $data['notification'] = $notification;
            $pusher->trigger($toUser->username, 'new-notification', $data);
        } catch (GuzzleException $guzzleException) {
            Log::error('Pusher guzzle exception: '.$guzzleException->getMessage());
        } catch (\Exception $exception) {
            Log::error('Pusher exception: '.$exception->getMessage());
        }
    }

    /**
     * Dispatches a reaction notification.
     *
     * @param $reaction
     * @return |null
     */
    public static function createNewReactionNotification($reaction)
    {
        $skip = false;
        if ($reaction->post_id != null) {
            $post = Post::query()->where('id', $reaction->post_id)->first();
            if ($post != null && $post->user_id === $reaction->user_id) {
                $skip = true;
            }
        }

        if ($reaction->post_comment_id != null) {
            $postComment = PostComment::query()->where('id', $reaction->post_comment_id)->first();
            if ($postComment != null && $postComment->user_id === $reaction->user_id) {
                $skip = true;
            }
        }

        if (! $skip) {
            return self::createAndPublishNotification(
                Notification::NEW_REACTION,
                null,
                null,
                null,
                null,
                null,
                $reaction
            );
        }
    }

    /**
     * Dispatches a new post comment notification.
     *
     * @param $reaction
     * @return |null
     */
    public static function createNewPostCommentNotification($postComment)
    {
        return self::createAndPublishNotification(
            Notification::NEW_COMMENT,
            null,
            null,
            $postComment,
            null,
            null,
            null
        );
    }

    /**
     * Dispatches a new sub notification.
     *
     * @param $reaction
     * @return |null
     */
    public static function createNewSubscriptionNotification($subscription)
    {
        return self::createAndPublishNotification(
            Notification::NEW_SUBSCRIPTION,
            null,
            null,
            null,
            $subscription,
            null,
            null
        );
    }

    /**
     * Dispatches a new tip notification.
     *
     * @param $reaction
     * @return |null
     */
    public static function createNewTipNotification($transaction)
    {
        return self::createAndPublishNotification(
            Notification::NEW_TIP,
            null,
            null,
            null,
            null,
            $transaction,
            null
        );
    }

    /**
     * Dispatches a withdrawal request change notification.
     *
     * @param $reaction
     * @return |null
     */
    public static function createApprovedOrRejectedWithdrawalNotification($withdrawal)
    {
        return self::createAndPublishNotification(
            Notification::WITHDRAWAL_ACTION,
            null,
            null,
            null,
            null,
            null,
            null,
            $withdrawal
        );
    }

    /**
     * Dispatches a new message notification.
     * @param $userMessage
     * @return |null
     */
    public static function createNewUserMessageNotification($userMessage)
    {
        return self::createAndPublishNotification(
            Notification::NEW_MESSAGE,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $userMessage
        );
    }

    /**
     * Dispatches a sub renewal notification.
     * @param $userMessage
     * @return |null
     */
    public static function sendSubscriptionRenewalEmailNotification($subscription, $succeeded)
    {
        if ($subscription != null) {
            if ($subscription->subscriber != null && $subscription->creator != null) {
                // send email for the user who initiated the subscription
                if (isset($subscription->subscriber->settings['notification_email_renewals'])
                    && $subscription->subscriber->settings['notification_email_renewals'] == 'true') {
                    $message = $succeeded ? __('successfully renewed') : __('failed renewing');
                    $buttonText = $succeeded ? __('Check out his profile for more content') : __('Go back to the website');
                    $buttonUrl = $succeeded ? route('profile', ['username' => $subscription->creator->username]) : route('home');

                    EmailsServiceProvider::sendGenericEmail(
                        [
                            'email' => $subscription->subscriber->email,
                            'subject' => __('Your subscription renewal'),
                            'title' => __('Hello, :name,', ['name'=>$subscription->subscriber->name]),
                            'content' =>  __('Email subscription updated', ['name'=>$subscription->creator->name, 'message'=>$message]),
                            'button' => [
                                'text' => $buttonText,
                                'url' => $buttonUrl,
                            ],
                        ]
                    );
                }
            }
        }
    }

    /**
     * Generate new tip notification
     * @param $transaction
     */
    public static function createTipNotificationByTransaction($transaction){
        if ($transaction != null && $transaction->status === Transaction::APPROVED_STATUS
            && ($transaction->type === Transaction::TIP_TYPE || $transaction->type === Transaction::CHAT_TIP_TYPE)) {
            self::createNewTipNotification($transaction);
        }
    }

    /**
     * Get notification filter type
     * @param $notification
     * @return string|null
     */
    public static function getNotificationFilterType($notification){
        $type = null;
        if ($notification != null) {
            switch ($notification->type) {
                case Notification::NEW_COMMENT:
                case Notification::NEW_MESSAGE:
                    $type = Notification::MESSAGES_FILTER;
                    break;
                case Notification::NEW_REACTION:
                    $type = Notification::LIKES_FILTER;
                    break;
                case Notification::NEW_SUBSCRIPTION:
                    $type = Notification::SUBSCRIPTIONS_FILTER;
                    break;
                case Notification::NEW_TIP:
                    $type = Notification::TIPS_FILTER;
                    break;
                case Notification::PROMOS_FILTER:
                    $type = Notification::PROMOS_FILTER;
                    break;
                case Notification::WITHDRAWAL_ACTION:
                    $type = Notification::WITHDRAWAL_ACTION;
                    break;
                default:
                    $type = false;
                    break;
            }
        }

        return $type;
    }

    /**
     * Gets the user un-read notifications
     * @return object
     */
    public static function getUnreadNotifications(){
        $unreadNotifications = [
            'total' => 0,
            Notification::MESSAGES_FILTER => 0,
            Notification::TIPS_FILTER => 0,
            Notification::SUBSCRIPTIONS_FILTER => 0,
            Notification::PROMOS_FILTER => 0,
            Notification::LIKES_FILTER => 0,
            Notification::WITHDRAWAL_ACTION => 0
        ];
        if(Auth::user()){
            $userId = Auth::user()->id;
            $userUnreadNotifications = Notification::where(['to_user_id' => $userId, 'read' => false])
                ->groupBy('type')->select('type', DB::raw('count(*) as total'))->get();
            if(count($userUnreadNotifications)){
                foreach ($userUnreadNotifications as $notification){
                    if(NotificationServiceProvider::getNotificationFilterType($notification)) {
                        $unreadNotifications[NotificationServiceProvider::getNotificationFilterType($notification)] += $notification->total;
                        $unreadNotifications['total'] += $notification->total;
                    }
                }
            }
        }

        return (object)$unreadNotifications;
    }

    /**
     * Gets the unread user messags
     * @return mixed
     */
    public static function getUnreadMessages(){
        $userID = Auth::user()->id;
        $blockedMembers  = UserListMember::select(['user_id'])->where('list_id', DB::raw(Auth::user()->lists->firstWhere('type', 'blocked')->id))->get()->pluck('user_id')->toArray();
        $count =  UserMessage::where('receiver_id',$userID)
            ->whereNotIn('sender_id',$blockedMembers)
            ->where('isSeen',0)
            ->count();
        return $count;
    }

    /**
     * Send deposit approved email notification for user
     * @param $transaction
     */
    public static function sendApprovedDepositTransactionEmailNotification($transaction) {
        if($transaction && $transaction->status === Transaction::APPROVED_STATUS && $transaction->type === Transaction::DEPOSIT_TYPE){
            EmailsServiceProvider::sendGenericEmail(
                [
                    'email' => $transaction->receiver->email,
                    'subject' => __('Your deposit request has been approved'),
                    'title' => __('Hello, :name,', ['name'=>$transaction->receiver->name]),
                    'content' =>  __('Your deposit request of :amount has been approved.', ['amount'=>$transaction->amount]),
                    'button' => [
                        'text' => __('Check your wallet'),
                        'url' => route('my.settings', ['type' => 'wallet']),
                    ],
                ]
            );
        }
    }

    /**
     * Send partially paid NowPayments transaction email notification for website admin
     * @param $transaction
     */
    public static function sendNowPaymentsPartiallyPaidTransactionEmailNotification($transaction) {
        if($transaction && $transaction->status === Transaction::PARTIALLY_PAID_STATUS){
            $adminEmails = User::where('role_id', 1)->select(['email', 'name'])->get();
            foreach ($adminEmails as $email) {
                EmailsServiceProvider::sendGenericEmail(
                    [
                        'email' => $email,
                        'subject' => __('Partially paid payment'),
                        'title' => __('Hello, :name,', ['name'=>'Admin']),
                        'content' =>  __('There is a partially paid payment done with NowPayments that requires your attention. (:paymentId)', ['paymentId' => $transaction->nowpayments_payment_id]),
                        'button' => [
                            'text' => __('Check payment'),
                            'url' => 'https://account.nowpayments.io/payments',
                        ],
                    ]
                );
            }
        }
    }

    /**
     * @param $stream
     */
    public static function createExpiringStreamNotifications($stream)
    {
        if($stream && $stream->user && $stream->status === Stream::IN_PROGRESS_STATUS) {
            // create website and email notifications
            return self::createAndPublishNotification(
                Notification::EXPIRING_STREAM,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $stream
            );
        }
    }
}
