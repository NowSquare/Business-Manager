<?php

namespace Orchestra\Contracts\Foundation\Listener;

interface AssetPublishing
{
    /**
     * Response to publishing asset failed.
     *
     * @param  array $errors
     *
     * @return mixed
     */
    public function publishingHasFailed(array $errors);

    /**
     * Response to publishing asset succeed.
     *
     * @return mixed
     */
    public function publishingHasSucceed();
}
