<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->string('name', 250)->nullable();
            $table->string('value_string', 250)->nullable();
            $table->mediumText('value_text')->nullable();
            $table->integer('value_int')->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->timestamp('value_date_time')->nullable();
            $table->date('value_date')->nullable();
            $table->time('value_time')->nullable();
            $table->json('value_json')->nullable();
            $table->ipAddress('value_ip_address')->nullable();
            $table->string('value_image_file_name')->nullable();
            $table->integer('value_image_file_size')->nullable();
            $table->string('value_image_content_type')->nullable();
            $table->timestamp('value_image_updated_at')->nullable();
            $table->string('value_image_variants', 255)->nullable();
        });

        Schema::create('tax_rates', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->default(1)->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->integer('rate')->unsigned();
            $table->boolean('default')->default(false);
        });

        Schema::create('units', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->default(1)->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name', 128);
            $table->boolean('default')->default(false);
        });

        Schema::create('industries', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->default(1)->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name', 128);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('industries');
        Schema::dropIfExists('units');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('settings');
    }
}
