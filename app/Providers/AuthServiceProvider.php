<?php

namespace App\Providers;

use App\Model\ReferralCodeUsage;
use App\Model\UserCode;
use App\Model\UserDevice;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class AuthServiceProvider extends ServiceProvider
{
    const ALPHABET = '0123456789ACDEFGHKMNPQRTVWXY';

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    /**
     * Function used to create an user
     * Used in the register function & installer process.
     *
     * @param $data
     * @return mixed
     */
    public static function createUser($data)
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => 'u'.time(),
            'password' => isset($data['password']) ? Hash::make($data['password']) : '',
            'settings' => collect([
                'notification_email_new_sub' => 'true',
                'notification_email_new_message' => env('notification_email_new_message', 'false'),
                'notification_email_expiring_subs' => 'true',
                'notification_email_renewals' => 'false',
                'notification_email_new_tip' => 'true',
                'notification_email_new_comment' => 'false',
                'locale' => getSetting('site.default_site_language'),
            ]),
            'enable_2fa' => false,
        ];
        if (isset($data['role_id'])) {
            $userData['role_id'] = $data['role_id'];
        }
        if (isset($data['email_verified_at'])) {
            $userData['email_verified_at'] = $data['email_verified_at'];
        }

        if (isset($data['auth_provider'])) {
            $userData['auth_provider'] = $data['auth_provider'];
        }
        if (isset($data['auth_provider_id'])) {
            $userData['auth_provider_id'] = $data['auth_provider_id'];
        }
        if(getSetting('security.default_2fa_on_register')){
            $userData['enable_2fa'] = true;
        }
        if(getSetting('site.default_profile_type_on_register') == 'free'){
            $userData['paid_profile'] = 0;
        }

        if(getSetting('site.default_user_privacy_setting_on_register') && getSetting('site.default_user_privacy_setting_on_register')  == 'private'){
            $userData['public_profile'] = false;
        }
        else{
            $userData['public_profile'] = true;
        }

        if(getSetting('site.default_profile_type_on_register') === 'open') {
            $userData['open_profile'] = true;
        }

        if(getSetting('payments.default_subscription_price')){
            $price = getSetting('payments.default_subscription_price');
            $userData['profile_access_price'] = $price;
            $userData['profile_access_price_6_months'] = $price;
            $userData['profile_access_price_12_months'] = $price;
        }

        try {
            $code = self::generateReferralCode(8);
            $userData['referral_code'] = $code;
        } catch (\Exception $exception){
        }


        $user = User::create($userData);

        if ($user != null) {
            GenericHelperServiceProvider::createUserWallet($user);
            ListsHelperServiceProvider::createUserDefaultLists($user->id);
            if(getSetting('security.default_2fa_on_register')) {
                self::addNewUserDevice($user->id, true);
            }
            if(getSetting('feed.default_users_to_follow')){
                $usersToFollow = explode(',',getSetting('feed.default_users_to_follow'));
                if(count($usersToFollow)){
                    foreach($usersToFollow as $userID){
                        ListsHelperServiceProvider::managePredefinedUserMemberList($user->id,$userID,'follow');
                    }
                }
            }
            if(getSetting('referrals.enabled')) {
                // Saving the referral even if the case
                if(Cookie::has('referral')){
                    $referralID =  User::where('referral_code', Cookie::get('referral'))->first();
                    if($referralID){
                        $existing = ReferralCodeUsage::where(['used_by' => $user->id, 'referral_code' => $referralID->referral_code])->first();
                        if(!$existing) {
                            ReferralCodeUsage::create(['used_by' => $user->id, 'referral_code' => $referralID->referral_code]);
                            Cookie::queue(Cookie::forget('referral'));
                        }
                    }
                }
            }
        }

        if (isset($data['auth_provider']) && isset($data['auth_provider_id'])){
            $user->sendEmailVerificationNotification();
        }

        return $user;
    }

    /**
     * Function that generates new 2FA codes and emails them
     */
    public static function generate2FACode()
    {
        try {
            $user = Auth::user();
            $code = rand(100000, 999999);
            UserCode::updateOrCreate(
                [ 'user_id' => $user->id ],
                [ 'code' => $code ]
            );
            App::setLocale($user->settings['locale']);
            EmailsServiceProvider::sendGenericEmail(
                [
                    'email' => $user->email,
                    'subject' => __('Verify your new device'),
                    'title' => __('Hello, :name,', ['name'=>$user->name]),
                    'content' => __('Your verification code is:') . ' ' .  $code,
                    'button' => [
                        'text' => __('Go to site'),
                        'url' => route('feed'),
                    ],
                ]
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Generates new string for current addr&agent
     * @return string
     */
    public static function generate2FaDeviceSignature(){
        return sha1(request()->ip().request()->header('User-Agent'));
    }

    /**
     * Adds a new user device
     * @param $userID
     * @param bool $verified
     * @return mixed
     */
    public static function addNewUserDevice($userID, $verified = false){
        $signature = self::generate2FaDeviceSignature();
        if(!UserDevice::where('signature',$signature)->where('user_id',$userID)->first()) {
            $data = [
                'user_id' => $userID,
                'address' => request()->ip(),
                'agent' => request()->header('User-Agent'),
                'signature' => $signature
            ];
            if ($verified) {
                $data['verified_at'] = Carbon::now();
            }
            return UserDevice::create($data);
        }
        return false;
    }

    /**
     * Gets validated user devices
     * @param $userID
     * @return mixed
     */
    public static function getUserDevices($userID){
        return UserDevice::where('user_id',$userID)->where('verified_at','<>',null)->select('signature')->pluck('signature')->toArray();
    }

    /**
     * @param $length
     * @return string
     * @throws \Exception
     */
    public static function generateReferralCode($length, $prefix = null) {
        $code = '';
        while (strlen($code) < $length || User::query()->where('referral_code', $code)->first() != null) {
            $code .= substr(self::ALPHABET, (random_int(1, 28) - 1), 1);
        }

        if (!empty($prefix)) {
            $code = $prefix . $code;
        }

        return $code;
    }

}
