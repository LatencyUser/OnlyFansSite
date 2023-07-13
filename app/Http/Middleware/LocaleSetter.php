<?php

namespace App\Http\Middleware;

use App\Providers\InstallerServiceProvider;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Cookie;

class LocaleSetter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If user doesn't have a locale, default to settings one, or config one
        if (! Session::has('locale')) {
            if (InstallerServiceProvider::checkIfInstalled()) {
                Session::put('locale', getSetting('site.default_site_language'));
            } else {
                Session::put('locale', Config::get('app.locale'));
            }
        }

        // If user has locale setting, use that one
        if (isset(Auth::user()->settings['locale'])) {
            App::setLocale(Auth::user()->settings['locale']);
        } else {
            // If cookie locale is present, use it
            if(Cookie::get('app_locale')){
                App::setLocale(Cookie::get('app_locale'));
            }
            else{
                $preferredLang = explode('-', $request->server('HTTP_ACCEPT_LANGUAGE'))[0] ?? null;
                if($preferredLang){
                    App::setLocale($preferredLang); // If user has missing locale setting - default on site setting
                }
                else{
                    App::setLocale(getSetting('site.default_site_language')); // If user has missing locale setting - default on site setting
                }
            }
        }

//        Custom Carbon language overrides sample
//        $carbonTranslations = Carbon::getTranslator();
//        $carbonTranslations->addResource('array', require base_path('resources/lang/ro/carbon.php'), 'ru');
//        $carbonTranslations->setLocale('ro');

        // Prepping the translation files for frontend usage
        $langPath = resource_path('lang/'.App::getLocale());
        if(!file_exists($langPath.'.json')){
            $langPath = resource_path('lang/en');
        }
        Session::put('translations', file_get_contents($langPath.'.json'));

        return $next($request);
    }
}
