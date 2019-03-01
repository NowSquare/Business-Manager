<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Add user
        DB::table('users')->insert([
          'account_id' => 1,
          'name' => 'System Owner',
          'email' => 'info@example.com',
          'password' => bcrypt('welcome'),
          'active' => true,
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (! config('app.demo')) {
          // Add company
          DB::table('companies')->insert([
            'account_id' => 1,
            'name' => 'Acme Corp',
            'default' => true,
            'active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
          ]);
        }

        Eloquent::unguard();

        $this->call('RolesPermissionsSeeder');
        $this->call('ProjectStatusesSeeder');
        $this->call('TaxRatesSeeder');
        $this->call('UnitsSeeder');
        $this->call('IndustriesSeeder');
        $this->call('CompanyTypesSeeder');

        if (config('app.demo')) {
          $this->call('DemoContentSeeder');
        }

        Eloquent::reguard();
    }
}
