<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Platform\Controllers\Core;

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
    protected $redirectTo = '/dashboard';
    protected $redirectAfterLogout = 'login';
  
    /**
     * Login screen.
     *
     * @return view
     */
    public function showLoginForm() {
      return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
     */
    public function login(Request $request) {
        $this->validateLogin($request);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

          // Check if user is active
          if (auth()->user()->active === 0) {
            auth()->guard('web')->logout();
            return redirect('login')->with('error', trans('g.account_inactive'));
          }

          // Authentication passed...

          // Update user
          auth()->user()->logins = auth()->user()->logins + 1;
          auth()->user()->last_login_ip_address =  request()->ip();
          auth()->user()->last_login = Carbon::now('UTC');
          auth()->user()->save();

          // Log
          Core\Log::add(
            'login', 
            trans('g.log_user_logged_in', ['name' => auth()->user()->name . ' (' . auth()->user()->email . ')']),
            '\App\User',
            auth()->user()->id
          );

          return redirect()->intended('dashboard');
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Logout, Clear Session, and Return.
     *
     * @return void
     */
    public function logout()
    {
        if (auth()->check()) {
          // Log admin back in
          $sl = \Session::pull('logout', '');
          if($sl != '') {
            $qs = Core\Secure::string2array($sl);
            \Auth::loginUsingId($qs['user_id'], true);
            return redirect('users');
          }

          // Log
          Core\Log::add(
            'logout', 
            trans('g.log_user_logged_out', ['name' => auth()->user()->name . ' (' . auth()->user()->email . ')']),
            '\App\User',
            auth()->user()->id
          );

          //$user = Auth::user();
          //Log::info('User Logged Out. ', [$user]);
          Auth::logout();
          Session::flush();
        }
        return redirect(trans('g.route_prefix') . 'login')->with('success', trans('g.logged_out_success'));
        //return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/')->with('success', trans('g.logged_out_success'));
    }
}
