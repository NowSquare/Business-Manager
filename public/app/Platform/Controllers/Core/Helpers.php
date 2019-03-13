<?php namespace Platform\Controllers\Core;

class Helpers extends \App\Http\Controllers\Controller {

  /**
   * Parse string for meta description use
   * \Platform\Controllers\Core\Helpers::parseDescription($string)
   */

  public static function parseDescription($string, $limit = 400) {
    $description = str_replace('"', '&quot;', preg_replace('/\s+/', ' ', preg_replace('/\r|\n/', ' ', strip_tags(html_entity_decode($string)))));
    $description = str_limit($description, $limit);
    $description = trim($description);
    return $description;
  }
}