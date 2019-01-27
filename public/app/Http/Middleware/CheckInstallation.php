<?php

namespace App\Http\Middleware;

use Closure;

class CheckInstallation
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Request::segment(1) != 'install') {
          if (! \Platform\Controllers\InstallationController::isInstalled()) {
            return redirect(url('install'));
            die();
          }
        }

        return $next($request);
    }
}