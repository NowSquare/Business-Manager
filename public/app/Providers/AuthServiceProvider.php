<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
          $admin = \Spatie\Permission\Models\Role::find(1);
          if ($user->hasRole($admin)) {
            return true;
          }
        });

        // Only admins can reset a client approval
        Gate::define('user-reset-approval-project-proposition', function ($user) {
          // Check if user has admin role
          if ($user->roles[0]->id == 1) {
            return true;
          }

          return false;
        });

        // Only clients who have permission to approve a proposition can do this
        Gate::define('user-approve-project-proposition', function ($user, $project) {
          // Check if user has client role
          if ($user->roles[0]->id == 5) {
            if ($project->client_can_view_tasks == 1) return true;
          }

          return false;
        });

        Gate::define('user-view-project-tasks', function ($user, $project) {
          if ($user->hasPermissionTo('view-project-tasks')) {
            return true;
          } else {
            // Check if user has client role
            if ($user->roles[0]->id == 5) {
              if ($project->client_can_view_tasks == 1) return true;
            }
          }
          return false;
        });

        Gate::define('user-view-project-comments', function ($user, $project) {
          if ($user->hasPermissionTo('view-comments')) {
            // Check if user has client role
            if ($user->roles[0]->id == 5) {
              if ($project->client_can_comment == 0) return false;
            }
            return true;
          }
          return false;
        });

        Gate::define('user-view-project-proposition', function ($user, $project) {
          if ($user->hasPermissionTo('view-project-proposition')) {
            // Check if user has client role
            if ($user->roles[0]->id == 5) {
              if ($project->client_can_view_proposition == 0) return false;
            }
            return true;
          }
          return false;
        });

        Gate::define('user-view-project-description', function ($user, $project) {
          if ($user->hasPermissionTo('view-project-description')) {
            // Check if user has client role
            if ($user->roles[0]->id == 5) {
              if ($project->client_can_view_description == 0) return false;
            }
            return true;
          }
          return false;
        });

        Gate::define('user-view-and-upload-personal-project-files', function ($user, $project) {
          if ($user->hasPermissionTo('view-and-upload-personal-project-files')) {
            // Check if user has client role
            if ($user->roles[0]->id == 5) {
              if ($project->client_can_upload_files == 0) return false;
            }
            return true;
          }
          return false;
        });
    }
}