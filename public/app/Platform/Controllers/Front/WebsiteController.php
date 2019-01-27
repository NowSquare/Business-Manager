<?php namespace Platform\Controllers\Front;

use App\Mail\SendLoginCode;
use Illuminate\Support\Facades\Mail;

class WebsiteController extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | Website Controller
   |--------------------------------------------------------------------------
   |
   | Front-end website related logic
   |--------------------------------------------------------------------------
   */

  /**
   * Index
   */

  public function getIndex() {
    return view('front.index');
  }

  /**
   * Terms and policy
   */

  public function getTerms() {
    return view('front.terms');
  }
}