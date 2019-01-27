<?php

namespace Orchestra\Contracts\Foundation\Command;

use Orchestra\Contracts\Foundation\Listener\ResourceLoader as Listener;

interface ResourceLoader
{
    /**
     * View list resources page.
     *
     * @param  \Orchestra\Contracts\Foundation\Listener\ResourceLoader  $listener
     *
     * @return mixed
     */
    public function index(Listener $listener);

    /**
     * View call a resource page.
     *
     * @param  \Orchestra\Contracts\Foundation\Listener\ResourceLoader  $listener
     * @param  string  $request
     *
     * @return mixed
     */
    public function show(Listener $listener, $request);
}
