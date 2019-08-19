<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadPopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_popups', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->default(1)->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('language', 5)->default('en');
            $table->string('timezone', 32)->default('UTC');
            $table->tinyInteger('variant')->unsigned()->default(1);
            $table->string('name', 200)->nullable();
            $table->string('local_domain', 64)->nullable();
            $table->string('domain', 200)->nullable();
            $table->boolean('active')->default(true);
            $table->dateTime('active_start')->nullable();
            $table->dateTime('active_end')->nullable();
            $table->json('active_week_days')->nullable();
            $table->text('url')->nullable();
            $table->mediumText('content')->nullable();
            $table->text('hosts')->nullable();
            $table->text('paths')->nullable();
            $table->text('referrer_hosts')->nullable();
            $table->text('referrer_paths')->nullable();
            $table->integer('views')->unsigned()->default(0);
            $table->integer('conversions')->unsigned()->default(0);
            $table->json('additional_fields')->nullable();
            $table->json('form_fields')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_popups');
    }
}
