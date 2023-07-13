<?php

namespace App\Http\Middleware;

use App;
use App\PlatformSettings;
use App\Providers\InstallerServiceProvider;
use App\UserBadge;
use App\UserStatus;
use Auth;
use Closure;
use JavaScript;
use Jenssegers\Agent\Agent;
use Session;
use Cookie;

class JavascriptVariables
{
    public function handle($request, Closure $next)
    {
        $mode = Cookie::get('app_theme');
        if(!$mode){
            $mode = getSetting('site.default_user_theme');
        }
        $jsData = [
            'debug' => env('APP_DEBUG'),
            'baseUrl' => url(''),
            'theme' => $mode
        ];
        if (InstallerServiceProvider::checkIfInstalled()) {
            $jsData['ppMode'] = getSetting('payments.paypal_live_mode') != null && getSetting('payments.paypal_live_mode') ? 'live' : 'sandbox';
            $jsData['showCookiesBox'] = getSetting('compliance.enable_cookies_box');
            $jsData['feedDisableRightClickOnMedia'] = getSetting('feed.disable_right_click');
            $jsData['currency'] = App\Providers\SettingsServiceProvider::getAppCurrencyCode();
            $jsData['currencySymbol'] = App\Providers\SettingsServiceProvider::getAppCurrencySymbol();
            $jsData['withdrawalsMinAmount'] = App\Providers\PaymentsServiceProvider::getWithdrawalMinimumAmount();
            $jsData['withdrawalsMaxAmount'] = App\Providers\PaymentsServiceProvider::getWithdrawalMaximumAmount();
            $jsData['depositMinAmount'] = App\Providers\PaymentsServiceProvider::getDepositMinimumAmount();
            $jsData['depositMaxAmount'] = App\Providers\PaymentsServiceProvider::getDepositMaximumAmount();
            $jsData['tipMinAmount'] = (int)getSetting('payments.min_tip_value');
            $jsData['tipMaxAmount'] = (int)getSetting('payments.max_tip_value');
            $jsData['stripeRecurringDisabled'] = getSetting('payments.stripe_recurring_disabled');
            $jsData['paypalRecurringDisabled'] = getSetting('payments.paypal_recurring_disabled');
            $jsData['ccBillRecurringDisabled'] = getSetting('payments.ccbill_recurring_disabled');
            $jsData['enable_age_verification_dialog'] = getSetting('compliance.enable_age_verification_dialog');
        }
        JavaScript::put(['app'=>$jsData]);

        if (Auth::check()) {
            JavaScript::put([
                'user' => [
                    'username' => Auth::user()->username,
                    'user_id' => Auth::user()->id,
                ],
                'socketsDriver' => getSetting('websockets.driver'),
                'pusher' => [
                    'cluster' => getSetting('websockets.pusher_app_cluster'),
                    'key' => getSetting('websockets.pusher_app_key'),
                    'logging' => env('PUSHER_APP_LOGGING', false),
                ],
                'soketi' => [
                    'key' => getSetting('websockets.soketi_app_key'),
                    'host' => getSetting('websockets.soketi_host_address'),
                    'port' => getSetting('websockets.soketi_host_port'),
                ],
                'appSettings' => [
                    'feed' => [
                        'allow_gallery_zoom' => getSetting('feed.allow_gallery_zoom') ? true : false
                    ],
                ]
            ]);
        }

        // Handling expired CSRF Tokens and Expired users sessions
        if (Session::has('sessionStatus') && Session::get('sessionStatus') == 'expired') {
            JavaScript::put(['app' => ['sessionStatus' => 'expired']]);
        }

        return $next($request);
    }
}
