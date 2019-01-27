<?php

use Illuminate\Database\Seeder;

class IndustriesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        // Inudstries
        DB::table('industries')->insert(['name' => 'Accounting & Legal']);
        DB::table('industries')->insert(['name' => 'Advertising']);
        DB::table('industries')->insert(['name' => 'Aerospace']);
        DB::table('industries')->insert(['name' => 'Agriculture']);
        DB::table('industries')->insert(['name' => 'Automotive']);
        DB::table('industries')->insert(['name' => 'Banking & Finance']);
        DB::table('industries')->insert(['name' => 'Biotechnology']);
        DB::table('industries')->insert(['name' => 'Broadcasting']);
        DB::table('industries')->insert(['name' => 'Business Services']);
        DB::table('industries')->insert(['name' => 'Commodities & Chemicals']);
        DB::table('industries')->insert(['name' => 'Communications']);
        DB::table('industries')->insert(['name' => 'Computers & Hightech']);
        DB::table('industries')->insert(['name' => 'Construction']);
        DB::table('industries')->insert(['name' => 'Defense']);
        DB::table('industries')->insert(['name' => 'Energy']);
        DB::table('industries')->insert(['name' => 'Entertainment']);
        DB::table('industries')->insert(['name' => 'Government']);
        DB::table('industries')->insert(['name' => 'Healthcare & Life Sciences']);
        DB::table('industries')->insert(['name' => 'Insurance']);
        DB::table('industries')->insert(['name' => 'Internet & Online']);
        DB::table('industries')->insert(['name' => 'Manufacturing']);
        DB::table('industries')->insert(['name' => 'Marketing']);
        DB::table('industries')->insert(['name' => 'Media']);
        DB::table('industries')->insert(['name' => 'Nonprofit & Higher Education']);
        DB::table('industries')->insert(['name' => 'Other']);
        DB::table('industries')->insert(['name' => 'Pharmaceuticals']);
        DB::table('industries')->insert(['name' => 'Photography']);
        DB::table('industries')->insert(['name' => 'Professional Services & Consulting']);
        DB::table('industries')->insert(['name' => 'Real Estate']);
        DB::table('industries')->insert(['name' => 'Restaurant & Catering']);
        DB::table('industries')->insert(['name' => 'Retail & Wholesale']);
        DB::table('industries')->insert(['name' => 'Sports']);
        DB::table('industries')->insert(['name' => 'Transportation']);
        DB::table('industries')->insert(['name' => 'Travel & Luxury']);

        Eloquent::reguard();
    }
}
