<?php

namespace App\Http\Controllers\Auth;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Plan;
use App\Providers\RouteServiceProvider;
use App\User;
use App\Vender;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if(!file_exists(storage_path() . "/installed"))
        {
            header('location:install');
            die;
        }
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if($user->delete_status == 0)
        {
            auth()->logout();
        }

        if($user->is_active == 0)
        {
            auth()->logout();
        }

        if($user->type == 'company')
        {
            $free_plan = Plan::where('price', '=', '0.0')->first();
            if($user->plan != $free_plan->id)
            {
                if(date('Y-m-d') > $user->plan_expire_date)
                {
                    $user->plan             = $free_plan->id;
                    $user->plan_expire_date = null;
                    $user->save();

                    $users     = User::where('created_by', '=', \Auth::user()->creatorId())->get();
                    $customers = Customer::where('created_by', '=', \Auth::user()->creatorId())->get();
                    $venders   = Vender::where('created_by', '=', \Auth::user()->creatorId())->get();

                    if($free_plan->max_users == -1)
                    {
                        foreach($users as $user)
                        {
                            $user->is_active = 1;
                            $user->save();
                        }
                    }
                    else
                    {
                        $userCount = 0;
                        foreach($users as $user)
                        {
                            $userCount++;
                            if($userCount <= $free_plan->max_users)
                            {
                                $user->is_active = 1;
                                $user->save();
                            }
                            else
                            {
                                $user->is_active = 0;
                                $user->save();
                            }
                        }

                    }


                    if($free_plan->max_customers == -1)
                    {
                        foreach($customers as $customer)
                        {
                            $customer->is_active = 1;
                            $customer->save();
                        }
                    }
                    else
                    {
                        $customerCount = 0;
                        foreach($customers as $customer)
                        {
                            $customerCount++;
                            if($customerCount <= $free_plan->max_customers)
                            {
                                $customer->is_active = 1;
                                $customer->save();
                            }
                            else
                            {
                                $customer->is_active = 0;
                                $customer->save();
                            }
                        }
                    }

                    if($free_plan->max_venders == -1)
                    {
                        foreach($venders as $vender)
                        {
                            $vender->is_active = 1;
                            $vender->save();
                        }
                    }
                    else
                    {
                        $venderCount = 0;
                        foreach($venders as $vender)
                        {
                            $venderCount++;
                            if($venderCount <= $free_plan->max_venders)
                            {
                                $vender->is_active = 1;
                                $vender->save();
                            }
                            else
                            {
                                $vender->is_active = 0;
                                $vender->save();
                            }
                        }
                    }

                    return redirect()->route('home')->with('error', 'Your plan expired limit is over, please upgrade your plan');
                }
            }

        }
    }

    public function showCustomerLoginForm($lang = '')
    {
        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.customer_login', compact('lang'));
    }

    public function customerLogin(Request $request)
    {

        $this->validate(
            $request, [
                        'email' => 'required|email',
                        'password' => 'required|min:6',
                    ]
        );

        if(\Auth::guard('customer')->attempt(
            [
                'email' => $request->email,
                'password' => $request->password,
            ], $request->get('remember')
        ))
        {
            if(\Auth::guard('customer')->user()->is_active == 0)
            {
                \Auth::guard('customer')->logout();
            }

            return redirect()->route('customer.dashboard');
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function showVenderLoginForm($lang = '')
    {
        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.vender_login', compact('lang'));
    }

    public function venderLogin(Request $request)
    {
        $this->validate(
            $request, [
                        'email' => 'required|email',
                        'password' => 'required|min:6',
                    ]
        );
        if(\Auth::guard('vender')->attempt(
            [
                'email' => $request->email,
                'password' => $request->password,
            ], $request->get('remember')
        ))
        {
            if(\Auth::guard('vender')->user()->is_active == 0)
            {
                \Auth::guard('vender')->logout();
            }

            return redirect()->route('vender.dashboard');
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function showLoginForm($lang = '')
    {

        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.login', compact('lang'));
    }

    public function showLinkRequestForm($lang = '')
    {

        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }


        \App::setLocale($lang);

        return view('auth.passwords.email', compact('lang'));
    }

    public function showCustomerLoginLang($lang = '')
    {
        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.customer_login', compact('lang'));
    }

    public function showVenderLoginLang($lang = '')
    {
        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.vender_login', compact('lang'));
    }

    //    ---------------------------------Customer ----------------------------------_
    public function showCustomerLinkRequestForm($lang = '')
    {
        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.passwords.customerEmail', compact('lang'));
    }

    public function postCustomerEmail(Request $request)
    {

        $request->validate(
            [
                'email' => 'required|email|exists:customers',
            ]
        );

        $token = \Str::random(60);

        DB::table('password_resets')->insert(
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]
        );

        Mail::send(
            'auth.customerVerify', ['token' => $token], function ($message) use ($request){
            $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $message->to($request->email);
            $message->subject('Reset Password Notification');
        }
        );

        return back()->with('status', 'We have e-mailed your password reset link!');
    }

    public function showResetForm(Request $request, $token = null)
    {

        $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
        $lang             = !empty($default_language) ? $default_language->value : 'en';

        \App::setLocale($lang);

        return view('auth.passwords.reset')->with(
            [
                'token' => $token,
                'email' => $request->email,
                'lang' => $lang,
            ]
        );
    }

    public function getCustomerPassword($token)
    {

        return view('auth.passwords.customerReset', ['token' => $token]);
    }

    public function updateCustomerPassword(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|exists:customers',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',

            ]
        );

        $updatePassword = DB::table('password_resets')->where(
                [
                    'email' => $request->email,
                    'token' => $request->token,
                ]
            )->first();

        if(!$updatePassword)
        {
            return back()->withInput()->with('error', 'Invalid token!');
        }

        $user = Customer::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return redirect('/login')->with('message', 'Your password has been changed.');

    }

    //    ----------------------------Vendor----------------------------------------------------
    public function showVendorLinkRequestForm($lang = '')
    {
        if($lang == '')
        {
            $lang = \App\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.passwords.vendorEmail', compact('lang'));
    }

    public function postVendorEmail(Request $request)
    {

        $request->validate(
            [
                'email' => 'required|email|exists:venders',
            ]
        );

        $token = \Str::random(60);

        DB::table('password_resets')->insert(
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]
        );

        Mail::send(
            'auth.vendorVerify', ['token' => $token], function ($message) use ($request){
            $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $message->to($request->email);
            $message->subject('Reset Password Notification');
        }
        );

        return back()->with('status', 'We have e-mailed your password reset link!');
    }

    public function getVendorPassword($token)
    {

        return view('auth.passwords.vendorReset', ['token' => $token]);
    }

    public function updateVendorPassword(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|exists:venders',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',

            ]
        );

        $updatePassword = DB::table('password_resets')->where(
            [
                'email' => $request->email,
                'token' => $request->token,
            ]
        )->first();

        if(!$updatePassword)
        {
            return back()->withInput()->with('error', 'Invalid token!');
        }

        $user = Vender::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return redirect('/login')->with('message', 'Your password has been changed.');

    }

}
