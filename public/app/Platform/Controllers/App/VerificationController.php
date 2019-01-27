<?php namespace Platform\Controllers\App;

use Carbon\Carbon;

use Platform\Controllers\Core;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

class VerificationController extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | Verification Controller
   |--------------------------------------------------------------------------
   |
   | Verification related logic
   |--------------------------------------------------------------------------
   */

  /**
   * Verify
   */

  public function getVerificationRequired() {
    if (auth()->check() && auth()->user()->email_verified_at !== null || auth()->user()->hasRole('Admin')) {
      return redirect('dashboard');
    } else {
      return view('auth.email.verification-required');
    }
  }

  /**
   * Verify email
   */

  public function verify($id) {
    $already_verified = false;
    $verified = false;
    $error = null;
    $success = null;

    $admin = \Spatie\Permission\Models\Role::find(1);

    if (auth()->check() && (auth()->user()->email_verified_at !== null || auth()->user()->hasRole($admin))) return redirect('dashboard');

    if (auth()->check()) {
      $user = auth()->user();
      if (auth()->user()->verification_code == $id && auth()->user()->email_verified_at == null) {
        auth()->user()->verification_code = null;
        auth()->user()->email_verified_at = Carbon::now('UTC');
        auth()->user()->save();
        auth()->logout();
        $verified = true;
      } elseif (auth()->user()->email_verified_at != null) {
        $already_verified = true;
      }
    } else {
      $user = \App\User::where('verification_code', $id)->whereNull('email_verified_at')->first();
      if ($user !== null) {
        $user->verification_code = null;
        $user->email_verified_at = Carbon::now('UTC');
        $user->save();
        $verified = true;
      }
    }

    if ($already_verified) {
      $error = trans('g.email_already_verified');
    } elseif ($verified) {
      // Log
      Core\Log::add(
        'verify', 
        trans('g.log_user_verified_email', ['name' => $user->name . ' (' . $user->email . ')']),
        '\App\User',
        $user->id,
        $user,
        $user->account_id
      );

      $success = trans('g.email_verified');
    } else {
      $error = trans('g.wrong_code_or_email_already_verified');
    }
    return view('auth.email.verify', compact('error', 'success'));
  }

  /**
   * Resend verification email
   */

  public function resend() {
    if (auth()->check()) {
      if (auth()->user()->verification_code !== null && auth()->user()->email_verified_at === null) {

        $verification_code = str_random(32);

        auth()->user()->verification_code = $verification_code;
        auth()->user()->save();

        // Send verification email
        $verification_url = url('email/verify/' . $verification_code);
        Mail::to(auth()->user()->email)->send(new VerifyEmail($verification_url, auth()->user()->name));

        return redirect('verification-required')->with('success', trans('g.verification_email_resent', ['email' => '<strong>' . auth()->user()->email . '</strong>']));
      }
    }

    return redirect('verification-required');
  }
}