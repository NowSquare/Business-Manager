<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Platform\Controllers\Core;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      // Fix for "Specified key was too long error" error
      // https://laravel-news.com/laravel-5-4-key-too-long-error
      \Schema::defaultStringLength(191);

      view()->share('favicon', url('favicon.ico'));
      view()->share('favicon_16', url('favicon-16x16.png'));
      view()->share('favicon_32', url('favicon-32x32.png'));
      view()->share('system_icon', url('assets/img/branding/icon.svg'));

      // Override config
      if (\Schema::hasTable('settings')) {
        config(['system.name' => Core\Settings::get('system_name', 'string', config('system.name'))]);
        $system_icon = Core\Settings::get('system_icon', 'image', null);
        if ($system_icon !== null) {
          view()->share('favicon', $system_icon->url('favicon'));
          view()->share('favicon_16', $system_icon->url('favicon'));
          view()->share('favicon_32', $system_icon->url('favicon'));
          view()->share('system_icon', $system_icon->url('icon-l'));
        }

        // Pusher
        if (
          config('broadcasting.connections.pusher.app_id') != '' && 
          config('broadcasting.connections.pusher.key') != '' && 
          config('broadcasting.connections.pusher.secret') != '' && 
          config('broadcasting.connections.pusher.options.cluster') != ''
        ) {
          $pusher_configured = true;
        } else {
          $pusher_configured = false;
          $pusher_app_id = Core\Settings::get('pusher_app_id', 'string', null);
          $pusher_key = Core\Settings::get('pusher_key', 'string', null);
          $pusher_secret = Core\Settings::get('pusher_secret', 'string', null);
          $pusher_cluster = Core\Settings::get('pusher_cluster', 'string', null);

          if (
            $pusher_app_id !== null && 
            $pusher_key !== null && 
            $pusher_secret !== null && 
            $pusher_cluster !== null
          ) {
            $pusher_configured = true;
            config(['broadcasting.connections.pusher.app_id' => $pusher_app_id]);
            config(['broadcasting.connections.pusher.key' => $pusher_key]);
            config(['broadcasting.connections.pusher.secret' => $pusher_secret]);
            config(['broadcasting.connections.pusher.options.cluster' => $pusher_cluster]);
          }
        }

        view()->share('pusher_configured', $pusher_configured);
      }

      Collection::macro('sortByDate', function ($column = 'created_at', $order = SORT_DESC) {
        /* @var $this Collection */
        return $this->sortBy(function ($datum) use ($column) {
            return strtotime($datum->$column);
        }, SORT_REGULAR, $order == SORT_DESC);
      });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
