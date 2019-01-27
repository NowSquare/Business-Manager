<?php

use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        // Units
        DB::table('units')->insert(['name' => 'day']);
        DB::table('units')->insert(['name' => 'hour', 'default' => 1]);
        DB::table('units')->insert(['name' => 'pcs']);
        DB::table('units')->insert(['name' => 'pkg']);
        DB::table('units')->insert(['name' => 'ft']);
        DB::table('units')->insert(['name' => 'lb']);
        DB::table('units')->insert(['name' => 'gal']);
        DB::table('units')->insert(['name' => 'meter']);
        DB::table('units')->insert(['name' => 'kg']);
        DB::table('units')->insert(['name' => 'l']);

        Eloquent::reguard();
    }
}
