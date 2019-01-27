<?php

use Illuminate\Database\Seeder;

class ProjectStatusesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        // Project statuses
        $grey = '#7b7b7b';
        DB::table('project_statuses')->insert(['name' => 'Backlog', 'color' => $grey, 'sort' => 1]);
        DB::table('project_statuses')->insert(['name' => 'Bid Stage', 'color' => $grey, 'sort' => 2]);
        DB::table('project_statuses')->insert(['name' => 'Contingent', 'color' => $grey, 'sort' => 3]);
        DB::table('project_statuses')->insert(['name' => 'Estimate', 'color' => $grey, 'sort' => 4]);
        DB::table('project_statuses')->insert(['name' => 'In Planning', 'color' => $grey, 'sort' => 5]);
        DB::table('project_statuses')->insert(['name' => 'In Setup', 'color' => $grey, 'sort' => 6]);
        DB::table('project_statuses')->insert(['name' => 'Inactive', 'color' => $grey, 'sort' => 7]);
        DB::table('project_statuses')->insert(['name' => 'Not Started', 'color' => $grey, 'sort' => 8]);
        DB::table('project_statuses')->insert(['name' => 'On Hold', 'color' => $grey, 'sort' => 9]);
        DB::table('project_statuses')->insert(['name' => 'Pipeline', 'color' => $grey, 'sort' => 10]);
        DB::table('project_statuses')->insert(['name' => 'Proposed', 'color' => $grey, 'sort' => 11]);
        DB::table('project_statuses')->insert(['name' => 'Prospect', 'color' => $grey, 'sort' => 12]);
        DB::table('project_statuses')->insert(['name' => 'Quality Control', 'color' => $grey, 'sort' => 13]);
        DB::table('project_statuses')->insert(['name' => 'Grey', 'color' => $grey, 'sort' => 14]);

        $light_green = '#58bd24';
        DB::table('project_statuses')->insert(['name' => 'Approved', 'color' => $light_green, 'sort' => 15]);
        DB::table('project_statuses')->insert(['name' => 'Confirmed', 'color' => $light_green, 'sort' => 16]);
        DB::table('project_statuses')->insert(['name' => 'Contingent', 'color' => $light_green, 'sort' => 17]);
        DB::table('project_statuses')->insert(['name' => 'Okay to Start', 'color' => $light_green, 'sort' => 18]);
        DB::table('project_statuses')->insert(['name' => 'Pending', 'color' => $light_green, 'sort' => 19]);
        DB::table('project_statuses')->insert(['name' => 'Ready', 'color' => $light_green, 'sort' => 20]);
        DB::table('project_statuses')->insert(['name' => 'Scheduled', 'color' => $light_green, 'sort' => 21]);
        DB::table('project_statuses')->insert(['name' => 'Quality Control', 'color' => $light_green, 'sort' => 22]);
        DB::table('project_statuses')->insert(['name' => 'Tech Setup', 'color' => $light_green, 'sort' => 23]);
        DB::table('project_statuses')->insert(['name' => 'Light Green', 'color' => $light_green, 'sort' => 24]);
        DB::table('project_statuses')->insert(['name' => 'Not Started', 'color' => $light_green, 'sort' => 25]);

        $green = '#007200';
        DB::table('project_statuses')->insert(['name' => 'Active', 'color' => $green, 'sort' => 26]);
        DB::table('project_statuses')->insert(['name' => 'In Development', 'color' => $green, 'sort' => 27]);
        DB::table('project_statuses')->insert(['name' => 'In Progress', 'color' => $green, 'sort' => 28, 'default_project' => 1, 'default_task' => 1]);
        DB::table('project_statuses')->insert(['name' => 'In Testing', 'color' => $green, 'sort' => 29]);
        DB::table('project_statuses')->insert(['name' => 'Live', 'color' => $green, 'sort' => 30]);
        DB::table('project_statuses')->insert(['name' => 'On Track', 'color' => $green, 'sort' => 31]);
        DB::table('project_statuses')->insert(['name' => 'Ready for Testing', 'color' => $green, 'sort' => 32]);
        DB::table('project_statuses')->insert(['name' => 'Started', 'color' => $green, 'sort' => 33]);
        DB::table('project_statuses')->insert(['name' => 'Quality Control', 'color' => $green, 'sort' => 34]);
        DB::table('project_statuses')->insert(['name' => 'UAT', 'color' => $green, 'sort' => 35]);
        DB::table('project_statuses')->insert(['name' => 'Green', 'color' => $green, 'sort' => 36]);

        $orange = '#fd9644';
        DB::table('project_statuses')->insert(['name' => 'Active', 'color' => $orange, 'sort' => 37]);
        DB::table('project_statuses')->insert(['name' => 'Alert', 'color' => $orange, 'sort' => 38]);
        DB::table('project_statuses')->insert(['name' => 'At Risk', 'color' => $orange, 'sort' => 39]);
        DB::table('project_statuses')->insert(['name' => 'Issue', 'color' => $orange, 'sort' => 40]);
        DB::table('project_statuses')->insert(['name' => 'Keep Watch', 'color' => $orange, 'sort' => 41]);
        DB::table('project_statuses')->insert(['name' => 'Late', 'color' => $orange, 'sort' => 42]);
        DB::table('project_statuses')->insert(['name' => 'Needs Review', 'color' => $orange, 'sort' => 43]);
        DB::table('project_statuses')->insert(['name' => 'On Hold', 'color' => $orange, 'sort' => 44]);
        DB::table('project_statuses')->insert(['name' => 'Over Budget', 'color' => $orange, 'sort' => 45]);
        DB::table('project_statuses')->insert(['name' => 'Past Due', 'color' => $orange, 'sort' => 46]);
        DB::table('project_statuses')->insert(['name' => 'Pending Approval', 'color' => $orange, 'sort' => 47]);
        DB::table('project_statuses')->insert(['name' => 'Priority', 'color' => $orange, 'sort' => 48]);
        DB::table('project_statuses')->insert(['name' => 'Requires Feedback', 'color' => $orange, 'sort' => 49]);
        DB::table('project_statuses')->insert(['name' => 'Requires Follow-up', 'color' => $orange, 'sort' => 50]);
        DB::table('project_statuses')->insert(['name' => 'Requires Research', 'color' => $orange, 'sort' => 51]);
        DB::table('project_statuses')->insert(['name' => 'Quality Control', 'color' => $orange, 'sort' => 52]);
        DB::table('project_statuses')->insert(['name' => 'UAT', 'color' => $orange, 'sort' => 53]);
        DB::table('project_statuses')->insert(['name' => 'Orange', 'color' => $orange, 'sort' => 54]);

        $red = '#ff4141';
        DB::table('project_statuses')->insert(['name' => 'Active', 'color' => $red, 'sort' => 55]);
        DB::table('project_statuses')->insert(['name' => 'Alert', 'color' => $red, 'sort' => 56]);
        DB::table('project_statuses')->insert(['name' => 'Blocked', 'color' => $red, 'sort' => 57]);
        DB::table('project_statuses')->insert(['name' => 'Cancelled', 'color' => $red, 'sort' => 58]);
        DB::table('project_statuses')->insert(['name' => 'Cancelled - Change Order', 'color' => $red, 'sort' => 59]);
        DB::table('project_statuses')->insert(['name' => 'Concern', 'color' => $red, 'sort' => 60]);
        DB::table('project_statuses')->insert(['name' => 'Late Payment', 'color' => $red, 'sort' => 61]);
        DB::table('project_statuses')->insert(['name' => 'On Hold', 'color' => $red, 'sort' => 62]);
        DB::table('project_statuses')->insert(['name' => 'Suspended', 'color' => $red, 'sort' => 63]);
        DB::table('project_statuses')->insert(['name' => 'Terminated', 'color' => $red, 'sort' => 64]);
        DB::table('project_statuses')->insert(['name' => 'Rejected', 'color' => $red, 'sort' => 65]);
        DB::table('project_statuses')->insert(['name' => 'Quality Control', 'color' => $red, 'sort' => 66]);
        DB::table('project_statuses')->insert(['name' => 'UAT', 'color' => $red, 'sort' => 67]);
        DB::table('project_statuses')->insert(['name' => 'Red', 'color' => $red, 'sort' => 68]);

        $blue = '#146eff';
        DB::table('project_statuses')->insert(['name' => 'Cancelled', 'color' => $blue, 'sort' => 69]);
        DB::table('project_statuses')->insert(['name' => 'Cancelled Confirmed', 'color' => $blue, 'sort' => 70]);
        DB::table('project_statuses')->insert(['name' => 'Closed', 'color' => $blue, 'sort' => 71]);
        DB::table('project_statuses')->insert(['name' => 'Completed', 'color' => $blue, 'sort' => 72]);
        DB::table('project_statuses')->insert(['name' => 'Delivered', 'color' => $blue, 'sort' => 73]);
        DB::table('project_statuses')->insert(['name' => 'Done', 'color' => $blue, 'sort' => 74]);
        DB::table('project_statuses')->insert(['name' => 'Shipped', 'color' => $blue, 'sort' => 75]);
        DB::table('project_statuses')->insert(['name' => 'Submitted', 'color' => $blue, 'sort' => 76]);
        DB::table('project_statuses')->insert(['name' => 'Quality Control', 'color' => $blue, 'sort' => 77]);
        DB::table('project_statuses')->insert(['name' => 'Blue', 'color' => $blue, 'sort' => 78]);

        Eloquent::reguard();
    }
}
