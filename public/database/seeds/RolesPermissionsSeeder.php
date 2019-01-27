<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        // Create roles
        $admin = Role::create(['name' => 'Admin', 'color' => '#ff4141']);
        $manager = Role::create(['name' => 'Manager', 'color' => '#58bd24']);
        $employee = Role::create(['name' => 'Employee', 'color' => '#146eff']);
        $contractor = Role::create(['name' => 'Contractor', 'color' => '#2e36cc']);
        $client = Role::create(['name' => 'Client', 'color' => '#35A3FF']);
        $agent = Role::create(['name' => 'Agent', 'color' => '#225f28']);
        $lead = Role::create(['name' => 'Lead', 'color' => '#fd9644']);

        // Create permissions
        $permissions = [
          'access-profile',
          'access-settings',
          'access-log',
          'access-all-files',

          // Users
          'all-users', // Limited to assigned records by default
          'list-users',
          'view-user',
          'login-as-user',
          'create-user',
          'edit-user',
          'delete-user',

          // Companies
          'all-companies', // Limited to assigned records by default
          'list-companies',
          'view-company',
          'create-company',
          'edit-company',
          'delete-company',

          // Projects
          'all-projects', // Limited to assigned records by default
          'list-projects',
          'view-project',
          'view-project-description',
          'create-project',
          'edit-project',
          'delete-project',

          // Project files
          'view-and-upload-all-project-files',
          'view-and-upload-personal-project-files',

          // Project tasks
          'view-project-tasks',
          'view-personal-project-tasks',
          'create-project-task',
          'edit-project-task',
          'mark-project-task-complete',
          'delete-project-task',

          // Project propositions
          'edit-project-proposition',
          'view-project-proposition',

          // Comments
          'view-comments',
          'create-comment',
          'edit-comment',
          'delete-comment',

          // Messaging
          'view-inbox',
          'send-message',
          'delete-message',
        ];

        foreach ($permissions as $permission) {
          Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $manager->givePermissionTo([
          'access-profile', 
          'list-projects',
          'view-project',
          'view-project-description',
          'edit-project',
          'view-and-upload-all-project-files',
          'view-project-tasks',
          'create-project-task',
          'edit-project-task',
          'mark-project-task-complete',
          'delete-project-task',
          'edit-project-proposition',
          'view-project-proposition',
          'view-comments',
          'create-comment',
          'view-inbox',
          'send-message',
          'delete-message'
        ]);

        $employee->givePermissionTo([
          'access-profile', 
          'list-projects',
          'view-project',
          'view-and-upload-personal-project-files',
          'view-project-description',
          'view-project-tasks',
          'mark-project-task-complete',
          'view-comments',
          'create-comment',
          'view-inbox',
          'send-message',
          'delete-message'
        ]);

        $contractor->givePermissionTo([
          'access-profile', 
          'list-projects',
          'view-project',
          'view-and-upload-personal-project-files',
          'view-project-tasks',
          'mark-project-task-complete',
          'view-comments',
          'create-comment',
          'view-inbox',
          'send-message',
          'delete-message'
        ]);

        $client->givePermissionTo([
          'access-profile', 
          'list-projects',
          'view-project',
          'view-and-upload-personal-project-files',
          'view-project-proposition',
          'view-comments',
          'create-comment',
          'view-inbox',
          'send-message',
          'delete-message'
        ]);

        $agent->givePermissionTo([
          'access-profile', 
          'list-projects',
          'view-project',
          'view-and-upload-personal-project-files',
          'view-project-proposition',
          'view-comments',
          'create-comment',
          'view-inbox',
          'send-message',
          'delete-message'
        ]);

        $lead->givePermissionTo([
          'access-profile'
        ]);

        // Assign role to users
        $user = \App\User::find(1);
        $user->assignRole($admin);

        Eloquent::reguard();
    }
}
