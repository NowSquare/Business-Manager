<?php

namespace Orchestra\Contracts\Config;

interface PackageRepository
{
    /**
     * Register a package for cascading configuration.
     *
     * @param  string  $package
     * @param  string  $hint
     * @param  string|null  $namespace
     *
     * @return void
     */
    public function package(string $package, string $hint, ?string $namespace = null): void;
}
