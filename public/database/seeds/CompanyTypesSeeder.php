<?php

use Illuminate\Database\Seeder;

class CompanyTypesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        // Company types
        DB::table('company_types')->insert(['name' => 'Client']);
        DB::table('company_types')->insert(['name' => 'Vendor']);
        DB::table('company_types')->insert(['name' => 'Supplier']);

        Eloquent::reguard();
    }
}
