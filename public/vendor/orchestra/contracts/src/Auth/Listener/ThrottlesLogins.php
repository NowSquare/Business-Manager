<?php

namespace Orchestra\Contracts\Auth\Listener;

interface ThrottlesLogins
{
    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  array  $input
     * @param  int  $seconds
     *
     * @return mixed
     */
    public function sendLockoutResponse(array $input, $seconds);
}
