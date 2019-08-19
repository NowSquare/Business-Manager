<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->default(1)->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('active')->default(true);

            $table->string('slug', 128)->nullable();
            $table->string('url', 250)->nullable();
            $table->string('name', 200)->nullable();
            $table->text('description')->nullable();
            $table->text('website')->nullable();
            $table->string('phone', 24)->nullable();
            $table->mediumText('content')->nullable();
            $table->mediumText('style')->nullable();
            $table->text('location')->nullable();
            $table->string('address')->nullable();
            $table->string('street')->nullable();
            $table->string('street_number', 12)->nullable();
            $table->string('postal_code', 12)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('state', 64)->nullable();
            $table->string('country', 5)->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->dateTime('valid_from_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->string('redemption_code', 250)->nullable();
            $table->integer('number_of_times_redeemed')->default(0);
            $table->dateTime('last_redemption')->nullable();
            $table->integer('total_amount_of_coupons')->default(0);
            $table->integer('amount_of_coupons_per_user')->nullable();
            $table->integer('can_be_redeemed_more_than_once')->nullable();
            $table->integer('views')->unsigned()->default(0);
            $table->integer('conversions')->unsigned()->default(0);

            $table->string('language', 5)->default('en');
            $table->string('timezone', 32)->default('UTC');
            $table->tinyInteger('variant')->unsigned()->default(1);

            $table->json('form_fields')->nullable();
            $table->json('additional_fields')->nullable();
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();

            // Image
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();

            // Favicon
            $table->string('favicon_file_name')->nullable();
            $table->integer('favicon_file_size')->nullable();
            $table->string('favicon_content_type')->nullable();
            $table->timestamp('favicon_updated_at')->nullable();

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
        Schema::dropIfExists('coupons');
    }
}
