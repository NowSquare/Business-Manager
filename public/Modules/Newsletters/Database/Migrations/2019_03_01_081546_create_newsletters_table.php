<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletters', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->default(1)->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('language', 5)->default('en');
            $table->string('timezone', 32)->default('UTC');
            $table->tinyInteger('variant')->unsigned()->default(1);
            $table->string('name', 200);
            $table->string('subject', 200);
            $table->string('from_name', 128);
            $table->string('from_email', 200);
            $table->boolean('active')->default(true);
            $table->text('preview')->nullable();
            $table->mediumText('content')->nullable();
            $table->mediumText('style')->nullable();
            $table->integer('number_of_recepients')->unsigned()->nullable();
            $table->integer('times_sent')->unsigned()->default(0);
            $table->dateTime('last_sent_date')->nullable();
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        Schema::create('newsletter_user', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('newsletter_id')->unsigned()->index();
            $table->foreign('newsletter_id')->references('id')->on('newsletters')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('newsletter_role', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('newsletter_id')->unsigned()->index();
            $table->foreign('newsletter_id')->references('id')->on('newsletters')->onDelete('cascade');
            $table->integer('role_id')->unsigned()->index();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('newsletter_lead_source', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('newsletter_id')->unsigned()->index();
            $table->foreign('newsletter_id')->references('id')->on('newsletters')->onDelete('cascade');
            $table->string('lead_source', 250)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletter_lead_source');
        Schema::dropIfExists('newsletter_role');
        Schema::dropIfExists('newsletter_user');
        Schema::dropIfExists('newsletters');
    }
}
