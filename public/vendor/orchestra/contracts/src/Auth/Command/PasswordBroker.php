<?php

namespace Orchestra\Contracts\Auth\Command;

use Orchestra\Contracts\Auth\Listener\PasswordReset;
use Orchestra\Contracts\Auth\Listener\PasswordResetLink;

interface PasswordBroker
{
    /**
     * Request to reset password.
     *
     * @param  \Orchestra\Contracts\Auth\Listener\PasswordResetLink  $listener
     * @param  array  $input
     *
     * @return mixed
     */
    public function store(PasswordResetLink $listener, array $input);

    /**
     * Reset the password.
     *
     * @param  \Orchestra\Contracts\Auth\Listener\PasswordReset  $listener
     * @param  array  $input
     *
     * @return mixed
     */
    public function update(PasswordReset $listener, array $input);
}
