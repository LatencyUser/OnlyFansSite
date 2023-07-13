<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyTwoFactorCodeRequest;
use App\Model\UserCode;
use App\Model\UserDevice;
use App\Providers\AuthServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Session;

class TwoFAController extends Controller
{
    /**
     * Returns the new device verification route
     * @return response()
     */
    public function index()
    {
        if(getSetting('security.enable_2fa')){
            if(Auth::user()->enable_2fa && !in_array(AuthServiceProvider::generate2FaDeviceSignature(), AuthServiceProvider::getUserDevices(Auth::user()->id))  ) {
                return view('pages.2fa-verify');
            }
        }
        return redirect()->route('home');
    }

    /**
     * Handles 2Fa code submit
     * @return response()alert
     */
    public function store(VerifyTwoFactorCodeRequest $request)
    {
        $code = UserCode::where('user_id', Auth::user()->id)
            ->where('code', $request->code)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->first();
        if (!is_null($code)) {
            $device = UserDevice::where('signature',AuthServiceProvider::generate2FaDeviceSignature())->first();
            $device->verified_at = Carbon::now();
            $device->save();
            Session::put('force2fa', false);
            return redirect()->route('home');
        }
        return back()->with('error', __('The code you entered is invalid.'));
    }

    /**
     * Resends the 2FA verification code
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function resend()
    {
        $code = UserCode::where('user_id', Auth::user()->id)
            ->where('updated_at', '>=', now()->subMinutes(1))
            ->first();
        if($code){
            return back()->with('error', __('Please wait a minute before generating another 2FA code.'));
        }
        AuthServiceProvider::generate2FACode();
        return back()->with('success', __('We sent you a new code over your email address.'));
    }

    /**
     * Deletes the requested device, if found
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDevice(Request $request){
        try {
            $signature = $request->get('signature');
            UserDevice::where('user_id',Auth::user()->id)->where('signature',$signature)->delete();
            return response()->json(['success' => true, 'message' => __('Device deleted.')]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [__('An internal error has occurred.')]]);
        }
    }

}
