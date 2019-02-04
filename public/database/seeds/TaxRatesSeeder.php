<?php

use Illuminate\Database\Seeder;

class TaxRatesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        // Tax rates
        DB::table('tax_rates')->insert(['rate' => 2400]);
        DB::table('tax_rates')->insert(['rate' => 2300]);
        DB::table('tax_rates')->insert(['rate' => 2200]);
        DB::table('tax_rates')->insert(['rate' => 2100]);
        DB::table('tax_rates')->insert(['rate' => 2000]);
        DB::table('tax_rates')->insert(['rate' => 1900]);
        DB::table('tax_rates')->insert(['rate' => 1800]);
        DB::table('tax_rates')->insert(['rate' => 1700]);
        DB::table('tax_rates')->insert(['rate' => 1050]);
        DB::table('tax_rates')->insert(['rate' => 1025]);
        DB::table('tax_rates')->insert(['rate' => 1000]);
        DB::table('tax_rates')->insert(['rate' => 950]);
        DB::table('tax_rates')->insert(['rate' => 900]);
        DB::table('tax_rates')->insert(['rate' => 800]);
        DB::table('tax_rates')->insert(['rate' => 750]);
        DB::table('tax_rates')->insert(['rate' => 600]);
        DB::table('tax_rates')->insert(['rate' => 0]);

        Eloquent::reguard();
    }
}
