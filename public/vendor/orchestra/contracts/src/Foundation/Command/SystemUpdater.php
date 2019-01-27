<?php

namespace Orchestra\Contracts\Foundation\Command;

use Orchestra\Contracts\Foundation\Listener\SystemUpdater as Listener;

interface SystemUpdater
{
    /**
     * Migrate Orchestra Platform components.
     *
     * @param  \Orchestra\Contracts\Foundation\Listener\SystemUpdater  $listener
     *
     * @return mixed
     */
    public function migrate(Listener $listener);
}
