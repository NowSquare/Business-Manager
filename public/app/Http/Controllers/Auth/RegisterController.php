<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Platform\Controllers\Core;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:64'],
            'email' => ['required', 'string', 'email', 'max:128', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'max:32'],
        ]);

        $validator->after(function ($validator) use($data) {
          if (! isset($data['terms']) || $data['terms'] !== '1') {
              $validator->errors()->add('terms', trans('g.agree_terms_required'));
          }
        });

        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $account_id = 1;

        $language = config('system.default_language');
        $verification_code = str_random(32);

        $user = User::create([
            'account_id' => $account_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'locale' => $language,
            'signup_ip_address' => request()->ip(),
            'verification_code' => $verification_code,
            'active' => true,
        ]);

        // Assign default role
        $default_role = \Spatie\Permission\Models\Role::find(config('system.default_signup_role'));
        $user->assignRole($default_role);

        // Log
        Core\Log::add(
          'signup', 
          trans('g.log_user_signed_up', ['name' => $user->name . ' (' . $user->email . ')']),
          '\App\User',
          $user->id,
          $user,
          $account_id
        );

        // Send verification email
        $verification_url = url('email/verify/' . $verification_code);
        Mail::to($data['email'])->send(new VerifyEmail($verification_url, $data['name']));

        return $user;
    }
}
