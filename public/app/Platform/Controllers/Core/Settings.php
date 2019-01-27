<?php namespace Platform\Controllers\Core;

class Settings extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | Settings Controller
   |--------------------------------------------------------------------------
   |
   | Settings related logic
   |--------------------------------------------------------------------------
   */

  /**
   * Get setting
   */
  public static function get($name, $column = 'string', $default = NULL, $user_id = 0) {
    //$return = \Cache::rememberForever('settings_' . $name . '_' . $user_id, function() use($name, $default, $user_id) {
      $setting = \Platform\Models\Core\Setting::where('name', $name)->where('user_id', $user_id)->first();

      if(! empty($setting)) {
        return $setting->{'value_' . $column};
      } elseif($default !== NULL) {
        return $default;
      } else {
        return NULL;
      }
    //});

    //return $return;
  }

  /**
   * Set setting
   */
  public static function set($name, $column = 'string', $value, $user_id = 0) {
    //\Cache::forget('settings_' . $name . '_' . $user_id);

    $setting = \Platform\Models\Core\Setting::where('name', $name)->where('user_id', $user_id);

    if($setting->exists()) {
      if($value === NULL) {
        $setting->delete();
      } else {
        $setting = $setting->first();
        $setting->{'value_' . $column} = $value;
        $setting->save();

        // Check if image is deleted
        if ($value === \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT) {
          $setting->delete();
        }
      }
    } elseif($value !== NULL) {
      $setting = new \Platform\Models\Core\Setting;

      $setting->user_id = $user_id;
      $setting->name = $name;
      $setting->{'value_' . $column} = $value;
      $setting->save();
    }
    return true;
  }
}