<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_types', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->default(1)->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name', 64)->nullable();
        });

        Schema::create('companies', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('company_type_id')->unsigned()->nullable();
            $table->foreign('company_type_id')->references('id')->on('company_types')->onDelete('set null');
            $table->boolean('default')->nullable();
            $table->boolean('active')->default(false);
            $table->string('name', 64)->nullable();
            $table->string('files_dir', 164)->nullable();
            $table->text('short_description')->nullable();
            $table->string('industry', 64)->nullable();
            $table->string('code', 64)->nullable();
            $table->string('legal_form', 64)->nullable();

            $table->string('email', 128)->nullable();
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

            $table->string('shipping_street1', 250)->nullable();
            $table->string('shipping_street2', 250)->nullable();
            $table->string('shipping_city', 64)->nullable();
            $table->string('shipping_state', 64)->nullable();
            $table->string('shipping_postal_code', 32)->nullable();
            $table->char('shipping_country_code', 2)->nullable();

            $table->string('vat_number', 250)->nullable();
            $table->string('id_number', 250)->nullable();
            $table->string('bank', 250)->nullable();
            $table->string('bank_id', 250)->nullable();
            $table->string('ecode_swift', 250)->nullable();
            $table->string('iban', 250)->nullable();

            $table->mediumText('notes')->nullable();

            // Logo
            $table->string('logo_file_name')->nullable();
            $table->integer('logo_file_size')->nullable();
            $table->string('logo_content_type')->nullable();
            $table->timestamp('logo_updated_at')->nullable();

            $table->json('additional_fields')->nullable();
            $table->json('tags')->nullable();
            $table->json('meta')->nullable();

            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        // Many-to-many relation
        Schema::create('company_user', function(Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('company_id')->unsigned()->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('company_types');
    }
}
