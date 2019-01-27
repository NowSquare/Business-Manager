<?php

namespace App\Http\Middleware;

use Closure;
use Platform\Controllers\Core;

class ElFinder
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    /*
     |--------------------------------------------------------------------------
     | Set ElFinder config
     |--------------------------------------------------------------------------
     */

    if (auth()->guard('web')->check()) {

      $type = session('elfinder.type', 'user');
      $user_id = session('elfinder.user_id', auth()->user()->id);
      $company_id = session('elfinder.company_id', null);
      $project_id = session('elfinder.project_id', null);

      // Files on user level
      if ($type == 'user') {
        $user = \App\User::find($user_id);
        if ($user === null) die();

        if ($user->files_dir === null) {
          $user->files_dir = str_slug($user->name);
          $user->save();
        }
        $files_dir = 'users/' . $user->files_dir . '-' . Core\Secure::staticHash($user_id * 10000) . '/';
        $alias = $user->name;
      }

      // Files on project user level
      if ($type == 'project' || $type == 'project-user' || $type == 'project-client') {
        $user = \App\User::find($user_id);
        if ($user === null || $company_id === null || $project_id === null) die();

        if ($user->files_dir === null) {
          $user->files_dir = str_slug($user->name);
          $user->save();
        }

        $company = \Platform\Models\Company::find($company_id);
        if ($company === null) die();

        if ($company->files_dir === null) {
          $company->files_dir = str_slug($company->name);
          $company->save();
        }

        $project = \Platform\Models\Project::find($project_id);
        if ($project === null) die();

        if ($project->files_dir === null) {
          $project->files_dir = str_slug($project->name);
          $project->save();
        }

        if ($type == 'project') {
          $files_dir = 'projects/' . $company->files_dir . '-' . Core\Secure::staticHash($company_id * 10000) . '/' . $project->files_dir . '-' . Core\Secure::staticHash($project_id * 10000);
        } elseif ($type == 'project-user') {
          $files_dir = 'projects/' . $company->files_dir . '-' . Core\Secure::staticHash($company_id) . '/' . $project->files_dir . '-' . Core\Secure::staticHash($project_id) . '/' . $user->files_dir . '-' . Core\Secure::staticHash($user_id * 10000);
        } elseif ($type == 'project-client') {
          $files_dir = 'projects/' . $company->files_dir . '-' . Core\Secure::staticHash($company_id * 10000) . '/' . $project->files_dir . '-' . Core\Secure::staticHash($project_id * 10000) . '/client-uploads';
        }

        $alias = trans('g.project_files');
      }

      $roots = [
        [
          'driver' => 'Flysystem',
          'autoload' => true,
          'filesystem' => new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local(public_path() . '/files/')),
          'path' => $files_dir,
          'URL' => url('/files/' . $files_dir),
          'accessControl' => 'access',
          'tmpPath' => public_path() . '/files/' . $files_dir . '/.tmp',
          'tmbURL' => url('files/' . $files_dir . '/.tmb'),
          'tmbPath' => public_path() . '/files/' . $files_dir . '/.tmb',
          'uploadMaxSize' => '64M',
          'tmbSize' => '100',
          'tmbCrop' => false,
          'alias' => $alias,
          'uploadDeny' => ['text/x-php'],
          'attributes' => [
            [
               'pattern' => '/.tmb/',
               'read' => false,
               'write' => false,
               'hidden' => true,
               'locked' => false
            ],
            [
               'pattern' => '/.quarantine/',
               'read' => false,
               'write' => false,
               'hidden' => true,
               'locked' => false
            ],
            [ // hide file types
              'pattern' => '/\.(php|py|pl|sh)$/i',
              'read'   => false,
              'write'  => false,
              'locked' => true,
              'hidden' => true
            ]
          ]
        ]
      ];

      app()->config->set('elfinder.roots', $roots);
    }

    return $next($request);
  }
}
