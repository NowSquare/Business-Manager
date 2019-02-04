<?php

namespace App;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole {

    /**
     * Override name column for translation.
     *
     * @return string
     */
    public function getNameAttribute() {
        return __($this->attributes['name']);
    }
}