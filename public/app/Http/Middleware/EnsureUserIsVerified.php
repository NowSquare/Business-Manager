<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && ! $request->user()->hasRole('Admin') && $request->user()->email_verified_at === null) {

            return $request->expectsJson()
                    ? abort(403, 'Your email address is not verified.')
                    : redirect('verification-required');
        }

        return $next($request);
    }
}
