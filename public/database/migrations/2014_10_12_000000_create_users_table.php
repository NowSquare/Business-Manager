<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('active')->default(false);
            $table->string('business_domain', 128)->nullable();
            $table->string('code', 64)->nullable();
            $table->string('name', 128)->nullable();
            $table->string('email', 200)->unique();
            $table->string('files_dir', 164)->nullable();
            $table->string('verification_code', 64)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('token')->nullable();

            $table->string('login_code', 64)->nullable();
            $table->timestamp('login_code_valid_until')->nullable();

            $table->char('currency_code', 3)->nullable();
            $table->string('language', 5)->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('timezone', 32)->nullable();
            $table->string('date_format', 24)->nullable();
            $table->string('time_format', 10)->nullable();
            $table->string('number_format', 10)->nullable();
            $table->tinyInteger('decimals')->nullable();
            $table->string('decimal_seperator', 6)->nullable();
            $table->string('thousands_seperator', 6)->nullable();
            $table->string('minus_sign', 6)->nullable();
            $table->boolean('use_parentheses_for_negative_numbers')->default(false);
            $table->tinyInteger('first_day_of_week')->nullable();

            $table->ipAddress('signup_ip_address')->nullable();
            $table->integer('logins')->default(0)->unsigned();
            $table->dateTime('last_login')->nullable();
            $table->ipAddress('last_login_ip_address')->nullable();
            $table->dateTime('expires')->nullable();

            $table->string('salutation', 32)->nullable();
            $table->string('first_name', 64)->nullable();
            $table->string('last_name', 64)->nullable();
            $table->string('job_title', 64)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('lead_source', 250)->nullable();
            $table->string('lead_status', 128)->nullable();

            $table->string('phone', 32)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('website', 250)->nullable();
            $table->string('fax', 32)->nullable();
            $table->string('street1', 250)->nullable();
            $table->string('street2', 250)->nullable();
            $table->string('city', 64)->nullable();
            $table->string('state', 64)->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->char('country_code', 2)->nullable();

            $table->integer('hourly_rate')->nullable();

            $table->mediumText('notes')->nullable();

            // Avatar
            $table->string('avatar_file_name')->nullable();
            $table->integer('avatar_file_size')->nullable();
            $table->string('avatar_content_type')->nullable();
            $table->timestamp('avatar_updated_at')->nullable();

            // Image
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();

            $table->json('additional_fields')->nullable();
            $table->json('settings')->nullable();
            $table->json('tags')->nullable();
            $table->json('meta')->nullable();

            $table->rememberToken();

            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        // Many-to-many relation
        Schema::create('user_assigned_user', function(Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('assigned_user_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
        });

        Schema::table('user_assigned_user', function($table) {
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_assigned_user');
        Schema::dropIfExists('users');
    }
}
