<?php namespace Platform\Models;

use Actuallymab\LaravelComment\Models\Comment as LaravelComment;

class Comment extends LaravelComment {

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at'];
    }

}